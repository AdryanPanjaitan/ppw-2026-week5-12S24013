<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $kategoris = Kategori::withCount('bukus')->orderBy('nama_kategori')->paginate(10);

        return view('kategori.index', compact('kategoris'));
    }

    public function create(): View
    {
        return view('kategori.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        Kategori::create($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show(Kategori $kategori): View
    {
        $bukus = $kategori->bukus()->latest()->paginate(10);

        return view('kategori.show', compact('kategori', 'bukus'));
    }

    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate($this->rules($kategori), $this->messages(), $this->attributes());

        $kategori->update($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->bukus()->exists()) {
            return redirect()->route('kategori.index')
                ->with('error', 'Kategori "'.$kategori->nama_kategori.'" masih memiliki buku, jadi tidak bisa dihapus.');
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }

    private function rules(?Kategori $kategori = null): array
    {
        return [
            'kode_kategori' => ['required', 'string', 'min:2', 'max:10',
                Rule::unique('kategoris', 'kode_kategori')->ignore($kategori?->id)],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah terdaftar.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
        ];
    }

    private function attributes(): array
    {
        return [
            'kode_kategori' => 'Kode kategori',
            'nama_kategori' => 'Nama kategori',
        ];
    }
}