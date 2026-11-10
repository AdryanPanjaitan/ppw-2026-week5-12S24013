<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('tersedia') && is_string($request->input('tersedia'))) {
            $request->merge(['tersedia' => filter_var($request->input('tersedia'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategoris,id'],
            'tersedia' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'sort' => ['nullable', Rule::in(['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = Buku::with('kategori')->withCount('peminjamans')
            ->when($validated['q'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query
                    ->where('judul', 'like', "%{$term}%")
                    ->orWhere('penulis', 'like', "%{$term}%")
                    ->orWhere('isbn', 'like', "%{$term}%"));
            })
            ->when(isset($validated['kategori_id']), fn ($query) => $query->where('kategori_id', $validated['kategori_id']))
            ->when(isset($validated['tersedia']), fn ($query) => $query->where('stok', $validated['tersedia'] ? '>' : '=', 0))
            ->orderBy($validated['sort'] ?? 'created_at', $validated['direction'] ?? 'desc');

        return BukuResource::collection($query->paginate($validated['per_page'] ?? 10));
    }

    public function store(Request $request): JsonResponse
    {
        $book = Buku::create($request->validate($this->rules()));

        return (new BukuResource($book->load('kategori')))->response()
            ->setStatusCode(201)->header('Location', route('api.v1.bukus.show', $book));
    }

    public function show(Buku $buku): BukuResource
    {
        return new BukuResource($buku->load(['kategori', 'peminjamans' => fn ($query) => $query->latest()]));
    }

    public function update(Request $request, Buku $buku): JsonResponse|BukuResource
    {
        $rules = $this->rules($buku);
        if ($request->isMethod('patch')) {
            $rules = array_map(fn (array $rule): array => array_merge(['sometimes'], $rule), $rules);
        }
        $buku->update($request->validate($rules));

        return new BukuResource($buku->fresh()->load('kategori'));
    }

    public function destroy(Request $request, Buku $buku): JsonResponse
    {
        if ($buku->peminjamans()->where('status', 'dipinjam')->exists()) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Conflict', 'status' => 409,
                'detail' => 'Buku masih memiliki peminjaman aktif.',
                'instance' => $request->getRequestUri(),
            ], 409, ['Content-Type' => 'application/problem+json']);
        }
        $buku->delete();

        return response()->noContent();
    }

    private function rules(?Buku $buku = null): array
    {
        return [
            'isbn' => ['required', 'string', 'max:17', Rule::unique('bukus', 'isbn')->ignore($buku?->id)],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'between:1900,'.date('Y')],
            'kategori_id' => ['required', 'integer', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string'],
        ];
    }
}
