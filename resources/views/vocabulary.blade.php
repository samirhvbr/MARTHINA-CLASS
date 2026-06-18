@extends('layout')

@section('content')
<div class="container mt-4 mt-lg-5">
    <div class="text-center mb-4">
        <span class="hero-chip mb-3">
            <i class="fas fa-book-open"></i>
            Biblioteca de palavras
        </span>
        <h1 class="text-center mb-3">
            @if($selectedSubject)
                Vocabulario de {{ $selectedSubjectLabel }}
            @else
                Vocabulario
            @endif
        </h1>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            @if($selectedSubject)
                @php
                    $selectedTheme = 'neutral';
                    $selectedIcon = 'shapes';

                    if (str_contains($selectedSubjectLabel, 'Ingl')) {
                        $selectedTheme = 'english';
                        $selectedIcon = 'language';
                    } elseif (str_contains($selectedSubjectLabel, 'Portugu')) {
                        $selectedTheme = 'portuguese';
                        $selectedIcon = 'book-open';
                    } elseif (str_contains($selectedSubjectLabel, 'Matem')) {
                        $selectedTheme = 'math';
                        $selectedIcon = 'calculator';
                    }
                @endphp
                <x-subject-pill :label="$selectedSubjectLabel" :theme="$selectedTheme" :icon="$selectedIcon" />
            @endif
            <x-difficulty-seal level="easy" :label="$groupedWords->count() . ' grupos'" icon="layer-group" />
        </div>
    </div>

    @if($selectedSubject)
        <div class="text-center mb-3">
            <a href="/subjects/{{ $selectedSubject->id }}" class="btn btn-outline-secondary">Voltar para {{ $selectedSubject->name }}</a>
        </div>
    @endif

    @if(isset($publicSubjectLegend) && $publicSubjectLegend->count())
        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
            @foreach($publicSubjectLegend as $subjectLegend)
                @php
                    $legendTheme = 'neutral';
                    $legendIcon = 'shapes';

                    if (str_contains($subjectLegend['label'], 'Ingl')) {
                        $legendTheme = 'english';
                        $legendIcon = 'language';
                    } elseif (str_contains($subjectLegend['label'], 'Portugu')) {
                        $legendTheme = 'portuguese';
                        $legendIcon = 'book-open';
                    } elseif (str_contains($subjectLegend['label'], 'Matem')) {
                        $legendTheme = 'math';
                        $legendIcon = 'calculator';
                    }
                @endphp
                <x-subject-pill :label="$subjectLegend['label']" :theme="$legendTheme" :icon="$legendIcon" />
            @endforeach
        </div>
    @endif

    @if($groupedWords->isNotEmpty())
        @foreach($groupedWords as $group)
            @php
                $groupTheme = 'neutral';
                $groupIcon = 'shapes';

                if (str_contains($group['subjectLabel'], 'Ingl')) {
                    $groupTheme = 'english';
                    $groupIcon = 'language';
                } elseif (str_contains($group['subjectLabel'], 'Portugu')) {
                    $groupTheme = 'portuguese';
                    $groupIcon = 'book-open';
                } elseif (str_contains($group['subjectLabel'], 'Matem')) {
                    $groupTheme = 'math';
                    $groupIcon = 'calculator';
                }
            @endphp
            <div class="card mb-4 p-2">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2 class="h4 mb-0">{{ $group['categoryDisplayLabel'] }}</h2>
                        <x-subject-pill :label="$group['subjectLabel']" :theme="$groupTheme" :icon="$groupIcon" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($group['words'] as $word)
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 list-card">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $word->english }}</h5>
                                        <p class="card-text">{{ $word->portuguese }}</p>
                                        @if($word->example)
                                            <p class="text-muted small">{{ $word->example }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="soft-panel text-center">
            <x-difficulty-seal level="easy" label="Nenhuma palavra disponivel" icon="book-open" />
        </div>
    @endif
</div>
@endsection
