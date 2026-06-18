@extends('layout')

@section('content')
@php
    $profileScore = \App\Models\Score::where('user_id', $user->id)->sum('score');
    $profileXp = \App\Models\Score::where('user_id', $user->id)->sum('xp');
    $profileCorrect = \App\Models\Score::where('user_id', $user->id)->where('correct', true)->count();
    $goldTrophies = \Illuminate\Support\Facades\Schema::hasTable('quiz_results') ? \App\Models\QuizResult::where('user_id', $user->id)->where('trophy', 'gold')->count() : 0;
    $silverTrophies = \Illuminate\Support\Facades\Schema::hasTable('quiz_results') ? \App\Models\QuizResult::where('user_id', $user->id)->where('trophy', 'silver')->count() : 0;
    $bronzeTrophies = \Illuminate\Support\Facades\Schema::hasTable('quiz_results') ? \App\Models\QuizResult::where('user_id', $user->id)->where('trophy', 'bronze')->count() : 0;
@endphp
<div class="row justify-content-center">
    <div class="col-lg-10">
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card p-4 h-100 text-center">
                    <div class="avatar-shell">
                        @if($user->avatar_path)
                            <img src="/profile/avatar/{{ $user->id }}" alt="Foto de perfil">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 144px; height: 144px; font-size: 3rem; font-weight: 700; color: #555; border-radius: 36px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <span class="hero-chip mb-3 mx-auto">
                        <i class="fas fa-id-badge"></i>
                        Meu perfil
                    </span>

                    <h1 class="h4 mb-1">{{ $user->displayName() }}</h1>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="mb-2">{{ $user->phone }}</p>
                    @endif
                    @if($user->bio)
                        <p class="small text-muted mb-3">{{ $user->bio }}</p>
                    @endif

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div class="profile-quick-stat">
                                <strong>{{ $profileScore }}</strong>
                                <div class="small text-muted">Pontos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="profile-quick-stat">
                                <strong>{{ $profileXp }}</strong>
                                <div class="small text-muted">XP</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="profile-quick-stat">
                                <strong>{{ $profileCorrect }}</strong>
                                <div class="small text-muted">Acertos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="profile-quick-stat">
                                <strong>{{ $goldTrophies + $silverTrophies + $bronzeTrophies }}</strong>
                                <div class="small text-muted">Trofeus</div>
                            </div>
                        </div>
                    </div>

                    <div class="soft-panel mt-4 text-start">
                        <h5 class="mb-3">Selos por materia</h5>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <x-subject-pill label="Ingles" theme="english" icon="language" />
                            <x-subject-pill label="Portugues" theme="portuguese" icon="book-open" />
                            <x-subject-pill label="Matematica" theme="math" icon="calculator" />
                        </div>

                        <h5 class="mb-3">Selos por dificuldade</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <x-difficulty-seal level="easy" />
                            <x-difficulty-seal level="normal" />
                            <x-difficulty-seal level="hard" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card p-4 p-md-5 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h2 class="h4 mb-1">Informacoes do perfil</h2>
                            <p class="text-muted mb-0">Atualize seus dados para deixar sua conta completa.</p>
                        </div>
                        <a href="/score" class="btn btn-outline-secondary">Ver meus pontos</a>
                    </div>

                    <form method="POST" action="/profile" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Sobrenome</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea id="bio" name="bio" rows="3" class="form-control">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Foto de perfil</label>
                            <input type="file" id="avatar" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Formatos aceitos: JPG, PNG e WEBP. Tamanho maximo de 2 MB.</div>
                        </div>

                        @if($user->avatar_path)
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="remove_avatar" name="remove_avatar">
                                <label class="form-check-label" for="remove_avatar">
                                    Remover foto atual
                                </label>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-custom">Salvar perfil</button>
                    </form>
                </div>

                <div class="soft-panel mb-4">
                    <h2 class="h4 mb-3">Conquistas</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <x-feature-stat :value="$goldTrophies" label="Trofeus de ouro" variant="gold" icon="crown" class="text-start h-100" />
                        </div>
                        <div class="col-md-4">
                            <x-feature-stat :value="$silverTrophies" label="Trofeus de prata" variant="silver" icon="medal" class="text-start h-100" />
                        </div>
                        <div class="col-md-4">
                            <x-feature-stat :value="$bronzeTrophies" label="Trofeus de bronze" variant="bronze" icon="award" class="text-start h-100" />
                        </div>
                    </div>
                </div>

                <div class="card p-4 p-md-5">
                    <h2 class="h4 mb-4">Seguranca da conta</h2>

                    <form method="POST" action="/profile/password">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Senha atual</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nova senha</label>
                            <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-outline-secondary">Atualizar senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
