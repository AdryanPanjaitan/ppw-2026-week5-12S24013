@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label for="kode_kategori" class="form-label">Kode kategori</label>
        <input type="text" id="kode_kategori" name="kode_kategori"
               value="{{ old('kode_kategori', $kategori->kode_kategori ?? '') }}"
               class="form-control @error('kode_kategori') is-invalid @enderror">
        @error('kode_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label for="nama_kategori" class="form-label">Nama kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori"
               value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}"
               class="form-control @error('nama_kategori') is-invalid @enderror">
        @error('nama_kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-del">Simpan</button>
    <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>