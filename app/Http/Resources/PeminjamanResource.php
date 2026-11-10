<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'kode_pinjam' => $this->kode_pinjam,
            'nim_peminjam' => $this->nim_peminjam,
            'nama_peminjam' => $this->nama_peminjam,
            'status' => $this->status,
            'tanggal_pinjam' => $this->tanggal_pinjam?->toDateString(),
            'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo?->toDateString(),
            'tanggal_kembali' => $this->tanggal_kembali?->toDateString(),
            'buku' => new BukuResource($this->whenLoaded('buku')),
            'links' => [
                'self' => route('api.v1.peminjamans.show', $this->id),
            ],
        ];

        if ($this->status === 'dipinjam') {
            $data['links']['kembali'] = route('api.v1.peminjamans.kembali', $this->id);
        }

        return $data;
    }
}
