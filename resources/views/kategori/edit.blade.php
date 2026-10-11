<x-layout title="Edit Kategori">
    <h1 class="h4 fw-bold text-del mb-3">Edit Kategori</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('kategori.update', $kategori) }}" method="POST" novalidate>
                @method('PUT')
                @include('kategori._form')
            </form>
        </div>
    </div>
</x-layout>