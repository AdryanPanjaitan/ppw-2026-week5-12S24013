<x-layout :title="$kategori->nama_kategori">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold text-del mb-0">{{ $kategori->nama_kategori }}</h1>
            <small class="text-muted">Kode: {{ $kategori->kode_kategori }}</small>
        </div>
        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>ISBN</th><th>Judul</th><th>Penulis</th><th>Stok</th></tr>
                </thead>
                <tbody>
                    @forelse ($bukus as $buku)
                        <tr>
                            <td>{{ $buku->isbn }}</td>
                            <td><a href="{{ route('buku.show', $buku) }}" class="text-del text-decoration-none fw-semibold">{{ $buku->judul }}</a></td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->stok }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada buku pada kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $bukus->links() }}</div>
</x-layout>