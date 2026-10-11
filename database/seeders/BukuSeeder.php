<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriIds = Kategori::pluck('id');

        Buku::factory()
            ->count(20)
            ->state(fn () => ['kategori_id' => $kategoriIds->random()])
            ->create();
    }
}