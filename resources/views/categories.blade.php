@extends('layout')

@section('content')
@php
    $selectedTheme = 'neutral';
    $selectedIcon = 'shapes';

    if (!empty($selectedSubjectLabel) && str_contains($selectedSubjectLabel, 'Ingl')) {
        $selectedTheme = 'english';
        $selectedIcon = 'language';
    } elseif (!empty($selectedSubjectLabel) && str_contains($selectedSubjectLabel, 'Portugu')) {
        $selectedTheme = 'portuguese';
        $selectedIcon = 'book-open';
    } elseif (!empty($selectedSubjectLabel) && str_contains($selectedSubjectLabel, 'Matem')) {
        $selectedTheme = 'math';
        $selectedIcon = 'calculator';
    }
@endphp
<div class="row justify-content-center">
    <div class="col-lg-10">
        @if(session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
        @endif
        <div class="card p-4 p-lg-5 mb-4">
            <div class="text-center mb-4">
                <span class="hero-chip mb-3">
                    <i class="fas fa-graduation-cap"></i>
                    Escolha sua proxima atividade
                </span>
                <h1 class="mb-3">
                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                    @if($selectedSubject)
                        Atividades de {{ $selectedSubjectLabel }}
                    @else
                        Escolha sua atividade
                    @endif
                </h1>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @if($selectedSubject)
                        <x-subject-pill :label="$selectedSubjectLabel" :theme="$selectedTheme" :icon="$selectedIcon" />
                    @endif
                    <x-difficulty-seal level="normal" label="Quiz por categoria" icon="list-check" />
                    <x-difficulty-seal level="easy" :label="$categories->count() . ' trilhas'" icon="route" />
                </div>
            </div>
            @if($selectedSubject)
                <div class="text-center mb-4">
                    <a href="/subjects/{{ $selectedSubject->id }}" class="btn btn-outline-secondary">Voltar para {{ $selectedSubject->name }}</a>
                </div>
            @endif
            <div class="row">
                @foreach($categories as $category)
                @php
                    $categoryTheme = 'neutral';
                    $categoryIcon = 'shapes';
                    $categoryThemeClass = 'subject-theme-neutral';

                    if (str_contains($subjectLabels[$category->id] ?? '', 'Ingl')) {
                        $categoryTheme = 'english';
                        $categoryIcon = 'language';
                        $categoryThemeClass = 'subject-theme-english';
                    } elseif (str_contains($subjectLabels[$category->id] ?? '', 'Portugu')) {
                        $categoryTheme = 'portuguese';
                        $categoryIcon = 'book-open';
                        $categoryThemeClass = 'subject-theme-portuguese';
                    } elseif (str_contains($subjectLabels[$category->id] ?? '', 'Matem')) {
                        $categoryTheme = 'math';
                        $categoryIcon = 'calculator';
                        $categoryThemeClass = 'subject-theme-math';
                    }
                @endphp
                <div class="col-md-4 mb-3">
                    <a href="/quiz/{{ $category->id }}" class="text-decoration-none d-block h-100">
                        <div class="card list-card subject-card text-center">
                            <div class="subject-icon {{ $categoryTheme === 'english' ? 'accent-blue' : ($categoryTheme === 'portuguese' ? 'accent-pink' : ($categoryTheme === 'math' ? 'accent-green' : 'accent-blue')) }} mx-auto">
                                <i class="fas fa-{{ $category->icon ?: ($category->subject?->icon ?? 'book') }}"></i>
                            </div>
                            <div class="mb-2">
                                <x-subject-pill :label="$subjectLabels[$category->id] ?? 'Geral'" :theme="$categoryTheme" :icon="$categoryIcon" />
                            </div>
                            <h4>{{ $category->name }}</h4>
                            <p class="small text-muted mb-3">{{ $category->description }}</p>
                            <div class="d-flex justify-content-center">
                                @if($category->quiz_type === 'multiple_choice')
                                    <x-difficulty-seal level="normal" label="Multipla escolha" icon="list-check" />
                                @else
                                    <x-difficulty-seal level="easy" label="Vocabulario" icon="book-open" />
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
