<x-layout title="Tambah Kategori">
    <h1 class="h4 fw-bold text-del mb-3">Tambah Kategori</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('kategori.store') }}" method="POST" novalidate>
                @include('kategori._form')
            </form>
        </div>
    </div>
</x-layout>