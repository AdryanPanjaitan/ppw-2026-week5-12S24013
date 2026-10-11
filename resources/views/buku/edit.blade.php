<x-layout title="Edit Buku">
    <h1 class="h4 fw-bold text-del mb-3">Edit Buku</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('buku.update', $buku) }}" method="POST" novalidate>
                @method('PUT')
                @include('buku._form')
            </form>
        </div>
    </div>
</x-layout>