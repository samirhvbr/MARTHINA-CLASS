@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card auth-hero p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <div class="auth-side text-center text-lg-start">
                        <span class="hero-chip mb-3">
                            <i class="fas fa-door-open"></i>
                            Bora aprender?
                        </span>
                        <h1 class="mb-3">Entrar</h1>
                        <p class="text-muted mb-4">Use seu e-mail para continuar os desafios, ganhar XP e acompanhar sua evolucao.</p>
                        <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Ilustracao de estudo">
                    </div>
                </div>

                <div class="col-lg-7">
                    @if($flashMessage)
                        <div class="alert alert-success">{{ $flashMessage }}</div>
                    @endif

                    @if($flashError)
                        <div class="alert alert-danger">{{ $flashError }}</div>
                    @endif

                    <form method="POST" action="/login">
                        @csrf
                        <input type="text" name="company" class="d-none" tabindex="-1" autocomplete="off">

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $formEmail }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="text-end mb-4">
                            <a href="/forgot-password" class="link-secondary text-decoration-none">Esqueci minha senha</a>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-lg">Entrar</button>
                            <a href="/register" class="btn btn-outline-secondary">Criar conta</a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <form method="POST" action="/guest-login">
                        @csrf
                        <input type="text" name="company" class="d-none" tabindex="-1" autocomplete="off">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-dark btn-lg">Entrar como visitante</button>
                        </div>
                        <p class="text-muted small text-center mt-3 mb-0">O progresso do visitante e temporario e sera apagado ao fechar o navegador.</p>
                    </form>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
