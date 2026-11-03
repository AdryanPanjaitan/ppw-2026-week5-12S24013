<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BukuController extends Controller
{
    public function index(Request $request): View
    {
        $bukus = Buku::with('kategori')->withCount('peminjamans')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();
                $query->where(function ($query) use ($term): void {
                    $query->where('judul', 'like', "%{$term}%")
                        ->orWhere('penulis', 'like', "%{$term}%")
                        ->orWhere('isbn', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->integer('kategori_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $statistik = [
            'judul' => DB::table('bukus')->count(),
            'stok' => DB::table('bukus')->sum('stok'),
            'dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        $request->session()->put('last_search', $request->query());

        return view('buku.index', compact('bukus', 'kategoris', 'statistik'));
    }

    public function create(): View
    {
        return view('buku.create', [
            'kategoris' => Kategori::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    public function show(Buku $buku): View
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku): View
    {
        return view('buku.edit', [
            'buku' => $buku,
            'kategoris' => Kategori::orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate($this->rules($buku), $this->messages(), $this->attributes());

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    public function destroy(Buku $buku): RedirectResponse
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus!');
    }

    private function rules(?Buku $buku = null): array
    {
        return [
            'isbn' => ['required', 'string', 'regex:/^(?:97[89][-\s]?)?\d{9}[\dX](?:[-\s]?\d{1,5})?(?:[-\s]?\d{1,7})?(?:[-\s]?[\dX])?$/',
                Rule::unique('bukus', 'isbn')->ignore($buku?->id)],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:150'],
            'tahun_terbit' => ['required', 'integer', 'between:1900,'.date('Y')],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'min.numeric' => ':attribute minimal :min.',
            'between.numeric' => ':attribute harus di antara :min dan :max.',
        ];
    }

    private function attributes(): array
    {
        return [
            'isbn' => 'ISBN', 'judul' => 'Judul', 'penulis' => 'Penulis',
            'penerbit' => 'Penerbit', 'tahun_terbit' => 'Tahun terbit',
            'kategori_id' => 'Kategori', 'stok' => 'Stok', 'sinopsis' => 'Sinopsis',
        ];
    }
}
