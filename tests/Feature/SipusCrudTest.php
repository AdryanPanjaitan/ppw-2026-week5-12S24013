<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
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
        $kategori = Kategori::factory()->create(['kode_kategori' => 'PRG']);
        Buku::factory()->create(['kategori_id' => $kategori->id]);

        $this->delete(route('kategori.destroy', $kategori))
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }
}
