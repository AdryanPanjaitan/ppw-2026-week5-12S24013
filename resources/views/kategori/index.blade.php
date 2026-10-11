<x-layout title="Daftar Kategori">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold text-del mb-0">Kategori Buku</h1>
        <a href="{{ route('kategori.create') }}" class="btn btn-del">+ Tambah Kategori</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>No</th><th>Kode</th><th>Nama kategori</th><th>Jumlah buku</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                        <tr>
                            <td>{{ $kategoris->firstItem() + $loop->index }}</td>
                            <td><span class="badge badge-del">{{ $kategori->kode_kategori }}</span></td>
                            <td class="fw-semibold">{{ $kategori->nama_kategori }}</td>
                            <td>{{ $kategori->bukus_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada kategori. Klik "Tambah Kategori" untuk membuat yang pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $kategoris->links() }}</div>
</x-layout>