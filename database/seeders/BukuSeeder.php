<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $pemrograman = Kategori::where('kode_kategori', 'PRG')->firstOrFail();
        $basisData = Kategori::where('kode_kategori', 'BSD')->firstOrFail();

        Buku::create([
            'isbn' => '9786021234501', 'judul' => 'Buku dengan Stok Habis',
            'penulis' => 'Tim SIPUS-Del', 'penerbit' => 'Penerbit Andi',
            'tahun_terbit' => 2024, 'kategori_id' => $pemrograman->id, 'stok' => 0,
            'sinopsis' => 'Sampel buku tanpa stok.',
        ]);
        Buku::create([
            'isbn' => '9786021234502', 'judul' => 'Buku dengan Satu Stok',
            'penulis' => 'Tim SIPUS-Del', 'penerbit' => 'Penerbit Andi',
            'tahun_terbit' => 2024, 'kategori_id' => $basisData->id, 'stok' => 1,
            'sinopsis' => 'Sampel buku dengan satu stok.',
        ]);
        Buku::factory()->count(18)->state(fn () => [
            'kategori_id' => Kategori::query()->inRandomOrder()->value('id'),
            'stok' => fake()->numberBetween(2, 25),
        ])->create();
    }
}
