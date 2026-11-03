<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $bukuSatuStok = Buku::where('isbn', '9786021234502')->firstOrFail();
        $bukuNormal = Buku::where('stok', '>', 2)->firstOrFail();

        $bukuSatuStok->update(['stok' => 0]);
        Peminjaman::create([
            'kode_pinjam' => 'PJ-SEED-0001',
            'buku_id' => $bukuSatuStok->id,
            'nim_peminjam' => '12S24013',
            'nama_peminjam' => 'Adryan Panjaitan',
            'tanggal_pinjam' => today()->subDays(2),
            'tanggal_jatuh_tempo' => today()->addDays(5),
            'status' => 'dipinjam',
        ]);
        Peminjaman::create([
            'kode_pinjam' => 'PJ-SEED-0002',
            'buku_id' => $bukuNormal->id,
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Peminjam Contoh',
            'tanggal_pinjam' => today()->subDays(10),
            'tanggal_jatuh_tempo' => today()->subDays(3),
            'tanggal_kembali' => today()->subDays(1),
            'status' => 'dikembalikan',
        ]);
    }
}
