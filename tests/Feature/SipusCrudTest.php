<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SipusCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_buku_index_uses_eager_loaded_category_and_paginates(): void
    {
        Kategori::factory()->create(['kode_kategori' => 'PRG']);
        Buku::factory()->count(11)->create(['kategori_id' => Kategori::first()->id]);

        $response = $this->get(route('buku.index'));

        $response->assertOk()->assertViewHas('bukus', fn ($bukus) => $bukus->count() === 10);
        $this->assertTrue($response->viewData('bukus')->first()->relationLoaded('kategori'));
    }

    public function test_buku_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::factory()->create(['kode_kategori' => 'PRG']);
        $data = [
            'isbn' => '9786021234567',
            'judul' => 'Pemrograman Laravel Modern',
            'penulis' => 'Adryan Panjaitan',
            'penerbit' => 'Penerbit Andi',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 5,
            'sinopsis' => 'Referensi pemrograman web.',
        ];

        $this->post(route('buku.store'), $data)->assertRedirect(route('buku.index'));
        $buku = Buku::firstOrFail();
        $this->assertSame($data['judul'], $buku->judul);

        $this->put(route('buku.update', $buku), [...$data, 'judul' => 'Pemrograman Laravel Terapan'])
            ->assertRedirect(route('buku.index'));
        $this->assertSame('Pemrograman Laravel Terapan', $buku->fresh()->judul);

        $this->delete(route('buku.destroy', $buku))->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_buku_validation_rejects_duplicate_isbn_short_title_and_negative_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::factory()->create(['kode_kategori' => 'PRG']);
        Buku::factory()->create(['isbn' => '9786021234567', 'kategori_id' => $kategori->id]);

        $response = $this->from(route('buku.create'))->post(route('buku.store'), [
            'isbn' => '9786021234567',
            'judul' => 'Mini',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => -1,
        ]);

        $response->assertRedirect(route('buku.create'))
            ->assertSessionHasErrors(['isbn', 'judul', 'stok']);
    }

    public function test_kategori_with_books_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::factory()->create(['kode_kategori' => 'PRG']);
        Buku::factory()->create(['kategori_id' => $kategori->id]);

        $this->delete(route('kategori.destroy', $kategori))
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }

    public function test_mutation_routes_require_authentication(): void
    {
        $kategori = Kategori::factory()->create();

        $this->get(route('buku.create'))->assertRedirect(route('login'));
        $this->get(route('kategori.create'))->assertRedirect(route('login'));
        $this->post(route('peminjaman.store', Buku::factory()->create(['kategori_id' => $kategori->id])), [])->assertRedirect(route('login'));
    }

    public function test_peminjaman_berhasil_mengurangi_stok_dan_pengembalian_menambahnya(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::factory()->create();
        $buku = Buku::factory()->create(['kategori_id' => $kategori->id, 'stok' => 1]);

        $this->post(route('peminjaman.store', $buku), [
            'nim_peminjam' => '12S24013',
            'nama_peminjam' => 'Adryan Panjaitan',
        ])->assertRedirect(route('buku.show', $buku));
        $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'stok' => 0]);

        $peminjaman = Peminjaman::firstOrFail();
        $this->patch(route('peminjaman.return', $peminjaman))->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'stok' => 1]);
        $this->assertDatabaseHas('peminjamans', ['id' => $peminjaman->id, 'status' => 'dikembalikan']);
    }

    public function test_peminjaman_ditolak_saat_stok_nol_dan_nim_tidak_bisa_meminjam_dua_kali(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::factory()->create();
        $habis = Buku::factory()->create(['kategori_id' => $kategori->id, 'stok' => 0]);

        $this->from(route('buku.show', $habis))->post(route('peminjaman.store', $habis), [
            'nim_peminjam' => '12S24013', 'nama_peminjam' => 'Adryan Panjaitan',
        ])->assertRedirect(route('buku.show', $habis))->assertSessionHas('error');
        $this->assertDatabaseMissing('peminjamans', ['buku_id' => $habis->id]);

        $buku = Buku::factory()->create(['kategori_id' => $kategori->id, 'stok' => 2]);
        $data = ['nim_peminjam' => '12S24013', 'nama_peminjam' => 'Adryan Panjaitan'];
        $this->post(route('peminjaman.store', $buku), $data)->assertRedirect();
        $this->from(route('buku.show', $buku))->post(route('peminjaman.store', $buku), $data)
            ->assertRedirect(route('buku.show', $buku))->assertSessionHas('error');
        $this->assertSame(1, $buku->fresh()->stok);
    }

    public function test_login_berhasil_dan_mengganti_id_sesi(): void
    {
        $user = User::factory()->create(['email' => 'pustakawan@example.test', 'password' => 'password123']);
        $this->withSession(['marker' => 'before-login']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email, 'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('login_timestamp');
        $this->assertSame('before-login', session('marker'));
    }
}
