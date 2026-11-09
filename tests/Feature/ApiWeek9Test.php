<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiWeek9Test extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_protected_catalog_mutation_use_sanctum(): void
    {
        $user = User::factory()->create(['email' => 'api@example.test', 'password' => 'password123']);
        $category = Kategori::factory()->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email, 'password' => 'password123',
        ])->assertOk()->json('data.token');

        $this->getJson('/api/v1/auth/me', ['Authorization' => 'Bearer '.$login])
            ->assertOk()->assertJsonPath('data.email', $user->email);
        $this->postJson('/api/v1/bukus', [
            'isbn' => '9786021234567', 'judul' => 'Buku API Teruji',
            'penulis' => 'Penulis', 'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024, 'kategori_id' => $category->id, 'stok' => 2,
        ], ['Authorization' => 'Bearer '.$login])->assertCreated()
            ->assertHeader('Location');
    }

    public function test_catalog_filters_and_problem_details_are_consistent(): void
    {
        $category = Kategori::factory()->create();
        Buku::factory()->create(['kategori_id' => $category->id, 'stok' => 0]);

        $this->getJson('/api/v1/bukus?tersedia=true')
            ->assertOk()->assertJsonPath('data', []);
        $this->postJson('/api/v1/bukus', [])
            ->assertStatus(401)->assertHeader('Content-Type', 'application/problem+json');
        $this->getJson('/api/v1/bukus/999999')
            ->assertNotFound()->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_api_loan_and_return_update_stock_and_prevent_duplicate(): void
    {
        $user = User::factory()->create();
        $category = Kategori::factory()->create();
        $book = Buku::factory()->create(['kategori_id' => $category->id, 'stok' => 1]);
        $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
        $payload = ['buku_id' => $book->id, 'nim_peminjam' => '12S24013', 'nama_peminjam' => 'Penguji'];

        $created = $this->postJson('/api/v1/peminjamans', $payload, $headers)
            ->assertCreated()->json('data.id');
        $this->postJson('/api/v1/peminjamans', $payload, $headers)->assertStatus(409);
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 0]);
        $this->patchJson("/api/v1/peminjamans/{$created}/kembali", [], $headers)
            ->assertOk()->assertJsonPath('data.status', 'dikembalikan');
        $this->assertDatabaseHas('bukus', ['id' => $book->id, 'stok' => 1]);
    }

    public function test_soap_clearance_reads_active_loans_and_returns_fault_for_invalid_xml(): void
    {
        $category = Kategori::factory()->create();
        $book = Buku::factory()->create(['kategori_id' => $category->id]);
        Peminjaman::create([
            'buku_id' => $book->id,
            'kode_pinjam' => 'PJ-SOAP-TEST',
            'nim_peminjam' => '12S24013',
            'nama_peminjam' => 'Penguji',
            'tanggal_pinjam' => today(),
            'tanggal_jatuh_tempo' => today()->addDays(7),
            'status' => 'dipinjam',
        ]);

        $this->get('/soap/sipus.wsdl')->assertOk()->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $xml = '<Envelope><Body><CheckClearance><nim_peminjam>12S24013</nim_peminjam></CheckClearance></Body></Envelope>';
        $this->call('POST', '/soap/sipus', [], [], [], ['CONTENT_TYPE' => 'text/xml'], $xml)
            ->assertOk()->assertSee('<active_loans>1</active_loans>', false)
            ->assertSee('<is_cleared>false</is_cleared>', false);
        $this->call('POST', '/soap/sipus', [], [], [], ['CONTENT_TYPE' => 'text/xml'], '<broken>')
            ->assertStatus(500)->assertSee('SOAP-ENV:Client', false);
    }

    public function test_external_book_reference_handles_openlibrary_response(): void
    {
        Http::fake(['openlibrary.org/*' => Http::response([
            'ISBN:9786021234567' => ['title' => 'Buku Eksternal', 'authors' => [['name' => 'Penulis']]],
        ])]);
        $category = Kategori::factory()->create();
        $book = Buku::factory()->create(['kategori_id' => $category->id, 'isbn' => '9786021234567']);

        $this->getJson("/api/v1/bukus/{$book->id}/referensi-eksternal")
            ->assertOk()->assertJsonPath('data.judul', 'Buku Eksternal');
    }
}
