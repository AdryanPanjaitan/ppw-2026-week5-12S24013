<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'stok' => $this->stok,
            'sinopsis' => $this->sinopsis,
            'kategori' => $this->whenLoaded('kategori', fn () => [
                'id' => $this->kategori->id,
                'kode' => $this->kategori->kode_kategori,
                'nama' => $this->kategori->nama_kategori,
            ]),
            'peminjamans_count' => $this->when(isset($this->peminjamans_count), $this->peminjamans_count),
            'links' => [
                'self' => route('api.v1.bukus.show', $this->id),
            ],
        ];

        if ($this->stok > 0) {
            $data['links']['pinjam_buku'] = route('api.v1.peminjamans.store');
        }

        if ($this->relationLoaded('peminjamans')) {
            $data['riwayat_peminjaman'] = PeminjamanResource::collection($this->peminjamans);
        }

        return $data;
    }
}
