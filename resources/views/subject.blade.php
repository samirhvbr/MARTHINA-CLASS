@extends('layout')

@section('content')
@php
    $subjectTheme = 'neutral';
    $subjectIcon = 'shapes';
    $subjectVariant = 'blue';
    $subjectHint = 'Escolha uma trilha para continuar praticando.';

    if (str_contains($subjectLabel, 'Ingl')) {
        $subjectTheme = 'english';
        $subjectIcon = 'language';
        $subjectVariant = 'blue';
        $subjectHint = 'Treine leitura, escrita e novas palavras em ingles.';
    } elseif (str_contains($subjectLabel, 'Portugu')) {
        $subjectTheme = 'portuguese';
        $subjectIcon = 'book-open';
        $subjectVariant = 'pink';
        $subjectHint = 'Explore textos, interpretacao e palavras do portugues.';
    } elseif (str_contains($subjectLabel, 'Matem')) {
        $subjectTheme = 'math';
        $subjectIcon = 'calculator';
        $subjectVariant = 'green';
        $subjectHint = 'Resolva desafios, contas e raciocinio com a matematica.';
    }

    $accentClass = 'accent-blue';

    if ($subjectVariant === 'pink') {
        $accentClass = 'accent-pink';
    } elseif ($subjectVariant === 'green') {
        $accentClass = 'accent-green';
    }
@endphp
<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">
        <div class="card subject-stage-hero p-4 p-lg-5 mb-4 subject-theme-{{ $subjectTheme }} text-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="hero-chip mb-3" style="background: rgba(255,255,255,0.18); color: #fff;">
                        <i class="fas fa-stars"></i>
                        Trilha da materia
                    </span>

                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <x-subject-pill :label="$subjectLabel" :theme="$subjectTheme" :icon="$subjectIcon" />
                        <x-difficulty-seal level="normal" label="Missao pronta" icon="rocket" />
                    </div>

                    <h1 class="display-5 mb-3 text-white">{{ $subject->name }}</h1>
                    <p class="lead mb-4 text-white">{{ $subjectHint }}</p>

                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <x-feature-stat :value="$activityCount" label="Categorias de atividade" :variant="$subjectVariant" icon="graduation-cap" class="h-100" />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-feature-stat :value="$vocabularyCount" label="Grupos de vocabulario" :variant="$subjectVariant" icon="book-open" class="h-100" />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-feature-stat :value="$hasVocabulary ? '2' : '1'" label="Jeitos de estudar" :variant="$subjectVariant" icon="compass" class="h-100" />
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 text-center">
                    <div class="avatar-badge avatar-badge-lg avatar-rank-neutral mx-auto mb-3 micro-float">
                        <span><i class="fas fa-{{ $subjectIcon }}"></i></span>
                    </div>
                    <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Ilustracao da trilha" class="hero-illustration micro-float micro-delay-2">
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <a href="{{ $activityUrl }}" class="text-decoration-none d-block h-100">
                    <div class="journey-card feature-stat feature-stat-{{ $subjectVariant }}">
                        <div class="feature-stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 class="mb-2">Atividades</h3>
                        <p class="mb-4">Resolva desafios por categoria e avance no seu ritmo.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{ $activityCount }} categorias disponiveis</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                @if($hasVocabulary)
                    <a href="{{ $vocabularyUrl }}" class="text-decoration-none d-block h-100">
                        <div class="journey-card card border-0">
                            <div class="subject-icon {{ $accentClass }}">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3 class="mb-2">Vocabulario</h3>
                            <p class="text-muted mb-4">Consulte palavras, exemplos e reforcos visuais desta materia.</p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="fw-bold">{{ $vocabularyCount }} grupos para explorar</span>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="journey-card card border-0 bg-light-subtle">
                        <div class="subject-icon {{ $accentClass }} opacity-75">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="mb-2">Vocabulario</h3>
                        <p class="text-muted mb-4">Esta trilha ainda nao possui grupos de vocabulario cadastrados.</p>
                        <x-difficulty-seal level="easy" label="Volte em breve" icon="hourglass-half" class="align-self-start" />
                    </div>
                @endif
            </div>
        </div>

        <div class="card p-4 p-lg-5 text-center">
            <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                <x-subject-pill :label="$subjectLabel" :theme="$subjectTheme" :icon="$subjectIcon" />
                <x-difficulty-seal level="easy" label="Aprender brincando" icon="face-smile" />
                <x-difficulty-seal level="hard" label="Valendo trofeu" icon="trophy" />
            </div>

            <h2 class="mb-2">Monte seu proprio caminho</h2>
            <p class="text-muted mb-4">Voce pode revisar a teoria, praticar nas atividades e depois acompanhar seu placar.</p>

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                <a href="/" class="btn btn-outline-secondary">Voltar para materias</a>
                <a href="/score" class="btn btn-outline-dark">Meus pontos</a>
            </div>
        </div>
    </div>
</div>
@endsection
