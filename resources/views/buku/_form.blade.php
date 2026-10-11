@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $buku->isbn ?? '') }}"
               class="form-control @error('isbn') is-invalid @enderror">
        @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="kategori_id" class="form-label">Kategori</label>
        <select id="kategori_id" name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
            <option value="">-- Pilih kategori --</option>
            @foreach ($kategoris as $kategori)
                <option value="{{ $kategori->id }}"
                    {{ (string) old('kategori_id', $buku->kategori_id ?? '') === (string) $kategori->id ? 'selected' : '' }}>
                    {{ $kategori->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="judul" class="form-label">Judul</label>
        <input type="text" id="judul" name="judul" value="{{ old('judul', $buku->judul ?? '') }}"
               class="form-control @error('judul') is-invalid @enderror">
        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="penulis" class="form-label">Penulis</label>
        <input type="text" id="penulis" name="penulis" value="{{ old('penulis', $buku->penulis ?? '') }}"
               class="form-control @error('penulis') is-invalid @enderror">
        @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="penerbit" class="form-label">Penerbit</label>
        <input type="text" id="penerbit" name="penerbit" value="{{ old('penerbit', $buku->penerbit ?? '') }}"
               class="form-control @error('penerbit') is-invalid @enderror">
        @error('penerbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="tahun_terbit" class="form-label">Tahun terbit</label>
        <input type="number" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}"
               class="form-control @error('tahun_terbit') is-invalid @enderror">
        @error('tahun_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="stok" class="form-label">Stok</label>
        <input type="number" id="stok" name="stok" value="{{ old('stok', $buku->stok ?? 0) }}"
               class="form-control @error('stok') is-invalid @enderror">
        @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="sinopsis" class="form-label">Sinopsis <span class="text-muted">(opsional)</span></label>
        <textarea id="sinopsis" name="sinopsis" rows="4"
                  class="form-control @error('sinopsis') is-invalid @enderror">{{ old('sinopsis', $buku->sinopsis ?? '') }}</textarea>
        @error('sinopsis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-del">Simpan</button>
    <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>