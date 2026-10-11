<x-layout :title="$buku->judul">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 fw-bold text-del">{{ $buku->judul }}</h1>
            <p class="text-muted mb-3">{{ $buku->penulis }} &middot; {{ $buku->penerbit }}, {{ $buku->tahun_terbit }}</p>

            <dl class="row mb-3">
                <dt class="col-sm-3">ISBN</dt>
                <dd class="col-sm-9">{{ $buku->isbn }}</dd>
                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9"><span class="badge badge-del">{{ $buku->kategori?->nama_kategori ?? 'Tidak tersedia' }}</span></dd>
                <dt class="col-sm-3">Stok</dt>
                <dd class="col-sm-9">{{ $buku->stok }} eksemplar</dd>
                <dt class="col-sm-3">Sinopsis</dt>
                <dd class="col-sm-9">{{ $buku->sinopsis ?: 'Belum ada sinopsis.' }}</dd>
            </dl>

            <div class="d-flex gap-2">
                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-del">Edit</a>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
</x-layout>