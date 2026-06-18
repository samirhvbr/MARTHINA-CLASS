@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card auth-hero p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <div class="auth-side text-center text-lg-start">
                        <span class="hero-chip mb-3">
                            <i class="fas fa-user-plus"></i>
                            Novo aventureiro
                        </span>
                        <h1 class="mb-3">Criar conta</h1>
                        <p class="text-muted mb-4">Monte seu perfil para liberar as materias, acumular trofeus e acompanhar suas conquistas.</p>
                        <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Ilustracao de cadastro">
                    </div>
                </div>

                <div class="col-lg-7">
                    @if($flashError)
                        <div class="alert alert-danger">{{ $flashError }}</div>
                    @endif

                    <form method="POST" action="/register">
                        @csrf
                        <input type="text" name="company" class="d-none" tabindex="-1" autocomplete="off">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $formName }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $formEmail }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="human_check" class="form-label">Quanto e {{ $firstNumber }} + {{ $secondNumber }}?</label>
                            <input type="number" class="form-control" id="human_check" name="human_check" required>
                            <div class="form-text">Pergunta rapida para bloquear cadastro automatico.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-lg">Cadastrar</button>
                            <a href="/login" class="btn btn-outline-secondary">Ja tenho conta</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
