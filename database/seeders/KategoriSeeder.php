<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode_kategori' => 'PRG', 'nama_kategori' => 'Pemrograman'],
            ['kode_kategori' => 'BSD', 'nama_kategori' => 'Basis Data'],
            ['kode_kategori' => 'JKM', 'nama_kategori' => 'Jaringan Komputer'],
            ['kode_kategori' => 'SIS', 'nama_kategori' => 'Sistem Informasi'],
            ['kode_kategori' => 'MTK', 'nama_kategori' => 'Matematika dan Statistika'],
        ];

        foreach ($data as $item) {
            Kategori::updateOrCreate(['kode_kategori' => $item['kode_kategori']], $item);
        }
    }
}