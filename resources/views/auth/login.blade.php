<x-layout title="Masuk">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm"><div class="card-body">
                <h1 class="h4 fw-bold text-del mb-3">Masuk Pustakawan</h1>
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control mb-3 @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', request()->cookie('del_last_user_email')) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <label class="form-label" for="password">Kata sandi</label>
                    <input class="form-control mb-3 @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Ingat saya</label></div>
                    <button class="btn btn-del w-100">Masuk</button>
                </form>
            </div></div>
        </div>
    </div>
</x-layout>
