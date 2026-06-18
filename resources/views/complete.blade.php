@extends('layout')

@section('content')
@php
    $subjectTheme = 'neutral';
    $subjectIcon = 'shapes';

    if (!empty($categorySubjectLabel) && str_contains($categorySubjectLabel, 'Ingl')) {
        $subjectTheme = 'english';
        $subjectIcon = 'language';
    } elseif (!empty($categorySubjectLabel) && str_contains($categorySubjectLabel, 'Portugu')) {
        $subjectTheme = 'portuguese';
        $subjectIcon = 'book-open';
    } elseif (!empty($categorySubjectLabel) && str_contains($categorySubjectLabel, 'Matem')) {
        $subjectTheme = 'math';
        $subjectIcon = 'calculator';
    }
@endphp
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card p-4 p-lg-5 text-center">
            <div class="card-body">
                <div class="row align-items-center g-4 mb-4 text-start text-lg-start">
                    <div class="col-lg-7">
                        <span class="hero-chip mb-3">
                            <i class="fas fa-star"></i>
                            Fase concluida
                        </span>

                        <h1 class="display-4 text-success mb-3">
                            <i class="fas fa-trophy me-2"></i>
                            Parabens!
                        </h1>

                        @if(!empty($categorySubjectLabel))
                            <div class="mb-3">
                                <x-subject-pill :label="$categorySubjectLabel" :theme="$subjectTheme" :icon="$subjectIcon" />
                            </div>
                        @endif

                        <h2 class="h3 mb-3">Voce completou o quiz de {{ $categoryDisplayLabel ?? $category->name }}!</h2>
                        <p class="text-muted mb-0">Continue explorando novas categorias para aumentar seu XP e ganhar mais trofeus.</p>
                    </div>

                    <div class="col-lg-5 text-center">
                        <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Resultado do quiz" class="hero-illustration">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <x-feature-stat :value="$categoryScore" label="Pontos nesta categoria" variant="blue" icon="star" class="text-start h-100" />
                    </div>
                    <div class="col-md-6">
                        <x-feature-stat :value="$total_questions" label="Total de perguntas" variant="pink" icon="list-check" class="text-start h-100" />
                    </div>
                </div>

                <div class="soft-panel mb-4 text-start text-lg-center">
                    @if($isGuest ?? false)
                        <h4>Score temporario da sessao:</h4>
                        <h2 class="text-warning">{{ $guestMetrics['score'] ?? 0 }} pontos</h2>
                        <h4 class="mt-3">XP temporario da sessao:</h4>
                        <h2 class="text-primary">{{ $guestMetrics['xp'] ?? 0 }} XP</h2>
                        <p class="small text-muted mt-3 mb-0">Essas metricas existem apenas enquanto o navegador permanecer aberto.</p>
                    @else
                        <h4>Seu Score Atual:</h4>
                        <h2 class="text-warning">{{ session('user_id') ? \App\Models\Score::where('user_id', session('user_id'))->sum('score') : \App\Models\Score::sum('score') }} pontos</h2>
                        <h4 class="mt-3">Seu XP Atual:</h4>
                        <h2 class="text-primary">{{ session('user_id') ? \App\Models\Score::where('user_id', session('user_id'))->sum('xp') : \App\Models\Score::sum('xp') }} XP</h2>
                    @endif
                </div>

                @if($isGuest ?? false)
                    @if(isset($trophy) && $trophy !== 'none')
                    <div class="mb-3 soft-panel">
                        <h3 class="mb-2">Trofeu conquistado</h3>
                        <x-difficulty-seal :level="$trophy == 'gold' ? 'normal' : ($trophy == 'silver' ? 'easy' : 'hard')" :label="ucfirst($trophy)" :icon="$trophy == 'gold' ? 'crown' : ($trophy == 'silver' ? 'medal' : 'award')" />
                    </div>
                    @endif

                    <div class="mb-4 soft-panel">
                        <h5>Seus trofeus</h5>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <x-feature-stat :value="$guestMetrics['trophies']['gold'] ?? 0" label="Ouro" variant="gold" icon="crown" class="text-start h-100" />
                            </div>
                            <div class="col-md-4">
                                <x-feature-stat :value="$guestMetrics['trophies']['silver'] ?? 0" label="Prata" variant="silver" icon="medal" class="text-start h-100" />
                            </div>
                            <div class="col-md-4">
                                <x-feature-stat :value="$guestMetrics['trophies']['bronze'] ?? 0" label="Bronze" variant="bronze" icon="award" class="text-start h-100" />
                            </div>
                        </div>
                    </div>
                @elseif(\Illuminate\Support\Facades\Schema::hasTable('quiz_results'))
                    @if(isset($trophy) && $trophy !== 'none')
                    <div class="mb-3 soft-panel">
                        <h3 class="mb-2">Trofeu conquistado</h3>
                        <x-difficulty-seal :level="$trophy == 'gold' ? 'normal' : ($trophy == 'silver' ? 'easy' : 'hard')" :label="ucfirst($trophy)" :icon="$trophy == 'gold' ? 'crown' : ($trophy == 'silver' ? 'medal' : 'award')" />
                    </div>
                    @endif

                    @if(session('user_id'))
                    <div class="mb-4 soft-panel">
                        <h5>Seus trofeus</h5>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <x-feature-stat :value="\App\Models\QuizResult::where('trophy','gold')->where('user_id', session('user_id'))->count()" label="Ouro" variant="gold" icon="crown" class="text-start h-100" />
                            </div>
                            <div class="col-md-4">
                                <x-feature-stat :value="\App\Models\QuizResult::where('trophy','silver')->where('user_id', session('user_id'))->count()" label="Prata" variant="silver" icon="medal" class="text-start h-100" />
                            </div>
                            <div class="col-md-4">
                                <x-feature-stat :value="\App\Models\QuizResult::where('trophy','bronze')->where('user_id', session('user_id'))->count()" label="Bronze" variant="bronze" icon="award" class="text-start h-100" />
                            </div>
                        </div>
                    </div>
                    @endif
                @endif

                <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                    <a href="/quiz/{{ $category->id }}/reset" class="btn btn-custom btn-lg">
                        <i class="fas fa-redo me-2"></i>Jogar Novamente
                    </a>
                    <a href="/lessons{{ $category->subject_id ? '?subject_id=' . $category->subject_id : '' }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-list me-2"></i>Escolher Outra Categoria
                    </a>
                    @if($category->isVocabularyQuiz())
                    <a href="/vocabulary{{ $category->subject_id ? '?subject_id=' . $category->subject_id : '' }}" class="btn btn-outline-dark btn-lg {{ ($isGuest ?? false) ? 'disabled' : '' }}" {{ ($isGuest ?? false) ? 'aria-disabled=true' : '' }}>
                        <i class="fas fa-book me-2"></i>Ver Vocabulario
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
