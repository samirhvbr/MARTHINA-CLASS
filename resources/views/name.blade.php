@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card p-4">
            <h1 class="text-center mb-4">Bem-vindo!</h1>
            <p>Digite seu nome para começarmos. Você não precisa de senha.</p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form method="POST" action="/name">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection