<x-layout title="Tambah Buku">
    <h1 class="h4 fw-bold text-del mb-3">Tambah Buku</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('buku.store') }}" method="POST" novalidate>
                @include('buku._form')
            </form>
        </div>
    </div>
</x-layout>