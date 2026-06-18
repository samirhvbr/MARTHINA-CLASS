@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <h1 class="mb-3">Nova senha</h1>
                <p class="text-muted mb-0">Cadastre uma nova senha para {{ $email }}.</p>
            </div>

            @if($flashError)
                <div class="alert alert-danger">{{ $flashError }}</div>
            @endif

            <form method="POST" action="/reset-password">
                @csrf
                <input type="text" name="company" class="d-none" tabindex="-1" autocomplete="off">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="password" class="form-label">Nova senha</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-custom btn-lg">Salvar nova senha</button>
                    <a href="/login" class="btn btn-outline-secondary">Voltar ao login</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
