<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $peminjamans = Peminjaman::with('buku')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    public function store(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate([
            'nim_peminjam' => ['required', 'string', 'max:30'],
            'nama_peminjam' => ['required', 'string', 'max:150'],
        ], [], [
            'nim_peminjam' => 'NIM peminjam',
            'nama_peminjam' => 'Nama peminjam',
        ]);

        try {
            DB::transaction(function () use ($validated, $buku): void {
                $buku = Buku::whereKey($buku->id)->lockForUpdate()->firstOrFail();

                if ($buku->stok < 1) {
                    throw new RuntimeException('Buku sedang tidak tersedia karena stok habis.');
                }

                $sudahMeminjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $validated['nim_peminjam'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sudahMeminjam) {
                    throw new RuntimeException('NIM tersebut masih meminjam buku yang sama.');
                }

                $buku->decrement('stok');
                Peminjaman::create([
                    ...$validated,
                    'buku_id' => $buku->id,
                    'kode_pinjam' => 'PJ-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                    'tanggal_pinjam' => today(),
                    'tanggal_jatuh_tempo' => today()->addDays(7),
                ]);
            }, 3);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION]: '.$e->getMessage(), ['exception' => $e]);

            return back()->withInput()->with('error', 'Transaksi peminjaman gagal diproses. Silakan coba lagi.');
        }

        return redirect()->route('buku.show', $buku)->with('success', 'Buku berhasil dipinjam.');
    }

    public function returnBook(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman): void {
                $peminjaman = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status !== 'dipinjam') {
                    throw new RuntimeException('Peminjaman ini sudah dikembalikan.');
                }

                $buku = Buku::whereKey($peminjaman->buku_id)->lockForUpdate()->firstOrFail();
                $peminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => today(),
                ]);
                $buku->increment('stok');
            }, 3);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION]: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Transaksi pengembalian gagal diproses. Silakan coba lagi.');
        }

        return redirect()->route('peminjaman.index')->with('success', 'Buku berhasil dikembalikan.');
    }
}
