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
                @auth
                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-del">Edit</a>
                    @if ($buku->stok > 0)
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#pinjamModal">Pinjam buku</button>
                    @endif
                @endauth
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>

    @auth
        <div class="modal fade" id="pinjamModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <form method="POST" action="{{ route('peminjaman.store', $buku) }}">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5">Form peminjaman</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <label class="form-label" for="nim_peminjam">NIM peminjam</label>
                        <input class="form-control mb-3" id="nim_peminjam" name="nim_peminjam" required>
                        <label class="form-label" for="nama_peminjam">Nama peminjam</label>
                        <input class="form-control" id="nama_peminjam" name="nama_peminjam" required>
                    </div>
                    <div class="modal-footer"><button class="btn btn-del">Simpan peminjaman</button></div>
                </form>
            </div></div>
        </div>
    @endauth
</x-layout>