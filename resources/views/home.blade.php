@extends('layout')

@section('content')
@php
    $homeXp = 0;

    if (session('is_guest')) {
        $homeXp = session('guest_metrics.xp', 0);
    } elseif (session('user_id')) {
        $homeXp = \App\Models\Score::where('user_id', session('user_id'))->sum('xp');
    }
@endphp
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card hero-card p-4 p-lg-5 mb-4">
            @if(session('message'))
                <div class="alert alert-success mb-4">{{ session('message') }}</div>
            @endif

            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="hero-chip mb-3">
                        <i class="fas fa-sparkles"></i>
                        Aprender brincando
                    </span>

                    <h1 class="display-4 mb-3">
                        @if(session('user_name'))
                            Ola, {{ session('user_name') }}!
                        @else
                            Bem-vindo ao Marthina!
                        @endif
                    </h1>

                    <p class="lead mb-4">Escolha uma materia e continue sua jornada com jogos, pontos e desafios.</p>

                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <x-difficulty-seal level="easy" label="Missoes rapidas" icon="wand-magic-sparkles" />
                        <x-difficulty-seal level="normal" label="Desafios por materia" icon="shapes" />
                        <x-difficulty-seal level="hard" label="XP e ranking" icon="trophy" />
                    </div>

                    @if(session('is_guest'))
                        <p class="small mb-2">Conta: visitante temporario</p>
                    @elseif(session('user_id'))
                        <p class="small mb-2">Conta: {{ session('user_email') }}</p>
                    @endif

                    <div class="row g-3 mt-1">
                        <div class="col-sm-4">
                            <x-feature-stat :value="count($subjectCards)" label="Materias abertas" variant="blue" icon="book-open" class="h-100" />
                        </div>
                        <div class="col-sm-4">
                            <x-feature-stat :value="$homeXp" label="XP acumulado" variant="gold" icon="bolt" class="h-100" />
                        </div>
                        <div class="col-sm-4">
                            <x-feature-stat :value="session('is_guest') ? 'Visitante' : 'Conta'" label="Modo atual" :variant="session('is_guest') ? 'pink' : 'green'" :icon="session('is_guest') ? 'user-clock' : 'user-check'" class="h-100" />
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 text-center">
                    <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Ilustracao de estudo" class="hero-illustration">
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            @foreach($subjectCards as $subjectCard)
            @php
                $subjectTheme = 'english';
                $subjectIconName = 'language';
                $subjectAccent = 'accent-blue';

                if (str_contains($subjectCard['name'], 'Portugu')) {
                    $subjectTheme = 'portuguese';
                    $subjectIconName = 'book-open';
                    $subjectAccent = 'accent-pink';
                } elseif (str_contains($subjectCard['name'], 'Matem')) {
                    $subjectTheme = 'math';
                    $subjectIconName = 'calculator';
                    $subjectAccent = 'accent-green';
                }
            @endphp
            <div class="col-md-6 col-xl-4 mb-4">
                <a href="{{ $subjectCard['url'] }}" class="text-decoration-none d-block h-100">
                    <div class="card subject-card p-4">
                        <div class="subject-icon {{ $subjectAccent }}">
                            <i class="fas fa-{{ $subjectCard['icon'] }}"></i>
                        </div>
                        <div class="mb-2">
                            <x-subject-pill :label="$subjectCard['label']" :theme="$subjectTheme" :icon="$subjectIconName" />
                        </div>
                        <h4 class="mb-2">{{ $subjectCard['name'] }}</h4>
                        <p class="text-muted mb-4">{{ $subjectCard['description'] }}</p>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="fw-bold">Explorar</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <div class="card p-4 p-lg-5">
            <div class="row justify-content-center align-items-stretch">
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="/score" class="text-decoration-none d-block h-100">
                        <div class="utility-card card text-center">
                            <i class="fas fa-trophy" style="background: linear-gradient(135deg, #ffb703, #fb8500);"></i>
                            <h5 class="mb-2">Pontuacao</h5>
                            <p class="text-muted mb-0">Acompanhe seus pontos e seu XP.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="/ranking" class="text-decoration-none d-block h-100">
                        <div class="utility-card card text-center">
                            <i class="fas fa-medal" style="background: linear-gradient(135deg, #6f5ef9, #9d7bff);"></i>
                            <h5 class="mb-2">Ranking</h5>
                            <p class="text-muted mb-0">Veja quem esta subindo mais rapido.</p>
                        </div>
                    </a>
                </div>
                @if(session('user_id'))
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="/profile" class="text-decoration-none d-block h-100">
                        <div class="utility-card card text-center">
                            <i class="fas fa-user-circle" style="background: linear-gradient(135deg, #3ecf8e, #29b6b1);"></i>
                            <h5 class="mb-2">Perfil</h5>
                            <p class="text-muted mb-0">Atualize sua conta e acompanhe seu progresso.</p>
                        </div>
                    </a>
                </div>
                @endif
                @if(session('is_admin'))
                <div class="col-md-6 col-lg-3 mb-3">
                    <a href="/admin" class="text-decoration-none d-block h-100">
                        <div class="utility-card card text-center border border-warning-subtle">
                            <i class="fas fa-user-shield" style="background: linear-gradient(135deg, #ff7b54, #ffb26b);"></i>
                            <h5 class="mb-2">Admin</h5>
                            <p class="text-muted mb-0">Gerencie usuarios, perguntas e categorias.</p>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
