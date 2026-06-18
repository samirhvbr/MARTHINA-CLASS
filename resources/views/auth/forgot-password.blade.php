@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <h1 class="mb-3">Recuperar senha</h1>
                <p class="text-muted mb-0">Informe seu e-mail para receber um link de redefinicao.</p>
            </div>

            @if($flashMessage)
                <div class="alert alert-success">{{ $flashMessage }}</div>
            @endif

            @if($flashError)
                <div class="alert alert-danger">{{ $flashError }}</div>
            @endif

            @if($usesLogMailer)
                <div class="alert alert-warning small">
                    O envio de e-mail neste ambiente esta configurado para log. Depois de enviar o formulario, procure o link em storage/logs/laravel.log.
                </div>
            @endif

            <form method="POST" action="/forgot-password">
                @csrf
                <input type="text" name="company" class="d-none" tabindex="-1" autocomplete="off">

                <div class="mb-4">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $formEmail }}" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-custom btn-lg">Enviar link</button>
                    <a href="/login" class="btn btn-outline-secondary">Voltar ao login</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
