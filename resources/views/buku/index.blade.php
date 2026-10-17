<x-layout title="Daftar Buku">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-bold text-del mb-1">Koleksi Buku</h1>
            <p class="text-muted mb-0">Kelola koleksi perpustakaan Institut Teknologi Del.</p>
        </div>
        @auth
            <a href="{{ route('buku.create') }}" class="btn btn-del">+ Tambah Buku</a>
        @endauth
    </div>

    <div class="row g-3 mb-3">
        @foreach ([
            ['label' => 'Total judul', 'value' => $statistik['judul']],
            ['label' => 'Stok tersedia', 'value' => $statistik['stok']],
            ['label' => 'Sedang dipinjam', 'value' => $statistik['dipinjam']],
            ['label' => 'Sudah dikembalikan', 'value' => $statistik['dikembalikan']],
        ] as $kartu)
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="text-muted small">{{ $kartu['label'] }}</div>
                    <div class="h3 text-del mb-0">{{ $kartu['value'] }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <form method="GET" class="card card-body shadow-sm mb-3">
        <div class="row g-2">
            <div class="col-md-6">
                <label for="q" class="visually-hidden">Cari</label>
                <input id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul, penulis, atau ISBN">
            </div>
            <div class="col-md-4">
                <label for="kategori_id" class="visually-hidden">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Cari</button></div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th><th>ISBN</th><th>Judul</th><th>Penulis</th>
                        <th>Kategori</th><th>Stok</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bukus as $buku)
                        <tr>
                            <td>{{ $bukus->firstItem() + $loop->index }}</td>
                            <td>{{ $buku->isbn }}</td>
                            <td class="fw-semibold">{{ $buku->judul }}</td>
                            <td>{{ $buku->penulis }}</td>
                            <td><span class="badge badge-del">{{ $buku->kategori?->nama_kategori ?? 'Tidak tersedia' }}</span></td>
                            <td><span class="badge {{ $buku->stok > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $buku->stok }}</span>
                                <small class="d-block text-muted">{{ $buku->peminjamans_count }} riwayat</small></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                @auth
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endauth
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada buku.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $bukus->links() }}</div>
</x-layout>
