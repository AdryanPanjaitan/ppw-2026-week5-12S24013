<x-layout title="Riwayat Peminjaman">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold text-del mb-0">Riwayat Peminjaman</h1>
        <form method="GET"><select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam</option>
            <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
        </select></form>
    </div>
    <div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Kode</th><th>Buku</th><th>Peminjam</th><th>Jatuh tempo</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>@forelse ($peminjamans as $peminjaman)
            <tr>
                <td>{{ $peminjaman->kode_pinjam }}</td><td>{{ $peminjaman->buku->judul }}</td>
                <td>{{ $peminjaman->nama_peminjam }}<br><small>{{ $peminjaman->nim_peminjam }}</small></td>
                <td>{{ $peminjaman->tanggal_jatuh_tempo->format('d-m-Y') }}</td>
                <td><span class="badge {{ $peminjaman->status === 'dipinjam' ? 'text-bg-warning' : 'text-bg-success' }}">{{ ucfirst($peminjaman->status) }}</span></td>
                <td>@if ($peminjaman->status === 'dipinjam')
                    <form method="POST" action="{{ route('peminjaman.return', $peminjaman) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success">Kembalikan</button></form>
                @else <small>{{ $peminjaman->tanggal_kembali?->format('d-m-Y') }}</small> @endif</td>
            </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">Belum ada riwayat peminjaman.</td></tr>@endforelse</tbody>
    </table></div></div>
    <div class="mt-3">{{ $peminjamans->links() }}</div>
</x-layout>
