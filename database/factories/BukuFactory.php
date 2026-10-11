<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    protected $model = Buku::class;

    public function definition(): array
    {
        $topik = fake()->randomElement([
            'Pemrograman Web', 'Basis Data', 'Jaringan Komputer', 'Sistem Informasi',
            'Kecerdasan Buatan', 'Aljabar Linear', 'Rekayasa Perangkat Lunak',
            'Keamanan Informasi', 'Komputasi Awan', 'Internet of Things',
            'Analisis Data', 'Interaksi Manusia dan Komputer',
        ]);

        $awalan = fake()->randomElement([
            'Pengantar', 'Dasar-Dasar', 'Konsep dan Penerapan',
            'Panduan Praktis', 'Teori dan Praktik',
        ]);

        return [
            'isbn' => fake()->unique()->isbn13(),
            'judul' => "{$awalan} {$topik}",
            'penulis' => fake()->name(),
            'penerbit' => fake()->randomElement([
                'Informatika Bandung', 'Penerbit Andi', 'Elex Media Komputindo',
                'Gramedia Pustaka Utama', 'Penerbit Deepublish', 'Graha Ilmu',
            ]),
            'tahun_terbit' => fake()->numberBetween(2005, (int) date('Y')),
            'kategori_id' => Kategori::factory(),
            'stok' => fake()->numberBetween(0, 25),
            'sinopsis' => "Buku ini membahas {$topik} secara bertahap, mulai dari konsep dasar "
                .'hingga contoh penerapan pada kasus nyata. Cocok dipakai sebagai referensi '
                .'perkuliahan maupun praktikum.',
        ];
    }
}