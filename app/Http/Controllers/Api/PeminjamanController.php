<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'nim_peminjam' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:dipinjam,dikembalikan'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $loans = Peminjaman::with('buku')
            ->when($validated['nim_peminjam'] ?? null, fn ($query, $nim) => $query->where('nim_peminjam', $nim))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate($validated['per_page'] ?? 10);

        return PeminjamanResource::collection($loans);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'max:30'],
            'nama_peminjam' => ['required', 'string', 'max:150'],
        ]);

        try {
            $loan = DB::transaction(function () use ($validated): Peminjaman {
                $book = Buku::whereKey($validated['buku_id'])->lockForUpdate()->firstOrFail();
                if (Peminjaman::where('buku_id', $book->id)->where('nim_peminjam', $validated['nim_peminjam'])->aktif()->exists()) {
                    throw new RuntimeException('Peminjam masih memiliki peminjaman aktif untuk buku ini.', 409);
                }
                if ($book->stok < 1) {
                    throw new RuntimeException('Stok buku sedang habis.', 422);
                }
                $book->decrement('stok');

                return Peminjaman::create([
                    ...$validated,
                    'kode_pinjam' => 'PJ-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                    'tanggal_pinjam' => today(),
                    'tanggal_jatuh_tempo' => today()->addDays(7),
                ]);
            }, 3);
        } catch (RuntimeException $exception) {
            return response()->json([
                'type' => 'about:blank',
                'title' => $exception->getCode() === 409 ? 'Conflict' : 'Unprocessable Content',
                'status' => $exception->getCode() ?: 422,
                'detail' => $exception->getMessage(),
                'instance' => $request->getRequestUri(),
            ], $exception->getCode() ?: 422, ['Content-Type' => 'application/problem+json']);
        }

        return (new PeminjamanResource($loan->load('buku')))->response()
            ->setStatusCode(201)->header('Location', route('api.v1.peminjamans.show', $loan));
    }

    public function show(Peminjaman $peminjaman): PeminjamanResource
    {
        return new PeminjamanResource($peminjaman->load('buku'));
    }

    public function kembali(Request $request, Peminjaman $peminjaman): PeminjamanResource|JsonResponse
    {
        try {
            $loan = DB::transaction(function () use ($peminjaman): Peminjaman {
                $loan = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
                if ($loan->status !== 'dipinjam') {
                    throw new RuntimeException('Peminjaman sudah dikembalikan.', 409);
                }
                $book = Buku::whereKey($loan->buku_id)->lockForUpdate()->firstOrFail();
                $loan->update(['status' => 'dikembalikan', 'tanggal_kembali' => today()]);
                $book->increment('stok');

                return $loan;
            }, 3);
        } catch (RuntimeException $exception) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Conflict', 'status' => 409,
                'detail' => $exception->getMessage(), 'instance' => $request->getRequestUri(),
            ], 409, ['Content-Type' => 'application/problem+json']);
        }

        return new PeminjamanResource($loan->load('buku'));
    }
}
