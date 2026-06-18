@extends('layout')

@section('content')
@php
    $topSubjectTheme = 'english';

    if (isset($publicSubjectLegend) && $publicSubjectLegend->count()) {
        $firstLegendLabel = $publicSubjectLegend[0]['label'] ?? '';

        if (str_contains($firstLegendLabel, 'Portugu')) {
            $topSubjectTheme = 'portuguese';
        } elseif (str_contains($firstLegendLabel, 'Matem')) {
            $topSubjectTheme = 'math';
        }
    }
@endphp
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="card p-4 p-lg-5 text-center">
            <div class="row align-items-center g-4 mb-4 text-start text-lg-start">
                <div class="col-lg-7">
                    <span class="hero-chip mb-3">
                        <i class="fas fa-medal"></i>
                        Seu painel de progresso
                    </span>
                    <h1 class="mb-3">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        @if(session('user_name'))
                            Pontuacao de {{ session('user_name') }}
                        @else
                            Sua Pontuacao
                        @endif
                    </h1>
                    @if($isGuest ?? false)
                        <p class="lead mb-0">Pontuacao temporaria de visitante. Ela sera apagada quando o navegador for fechado.</p>
                    @else
                        <p class="lead mb-0">Otimo trabalho! Continue aprendendo e subindo no ranking.</p>
                    @endif
                </div>

                <div class="col-lg-5 text-center">
                    <img src="{{ asset('assets/marthina-theme/images/knowledge-base.png') }}" alt="Painel de pontuacao" class="hero-illustration">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <x-feature-stat :value="$score . ' ⭐'" label="Pontuacao total" variant="blue" icon="star" class="text-start h-100" />
                </div>
                <div class="col-md-6">
                    <x-feature-stat :value="$xp . ' XP'" label="XP acumulado" variant="gold" icon="bolt" class="text-start h-100" />
                </div>
            </div>

            @if(isset($publicSubjectLegend) && $publicSubjectLegend->count())
            <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
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

            @if(isset($goldCount))
            <div class="soft-panel mb-4">
                <h5>Trofeus conquistados</h5>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <x-feature-stat :value="$goldCount" label="Trofeus de ouro" variant="gold" icon="crown" class="text-start h-100" />
                    </div>
                    <div class="col-md-4">
                        <x-feature-stat :value="$silverCount" label="Trofeus de prata" variant="silver" icon="medal" class="text-start h-100" />
                    </div>
                    <div class="col-md-4">
                        <x-feature-stat :value="$bronzeCount" label="Trofeus de bronze" variant="bronze" icon="award" class="text-start h-100" />
                    </div>
                </div>
            </div>
            @endif

            @if(isset($currentUserRank) && $currentUserRank)
            <div class="soft-panel mb-4">
                <h5>Sua posicao no ranking</h5>
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    <x-avatar-badge :name="session('user_name', 'U')" :rank="$currentUserRank->rank <= 3 ? $currentUserRank->rank : null" size="md" />
                    <div>
                        <p class="mb-1">Posicao: <strong>#{{ $currentUserRank->rank }}</strong></p>
                        <p class="text-muted mb-0">Score {{ $currentUserRank->total_score }} | XP {{ $currentUserRank->total_xp }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($isGuest ?? false)
            <p class="text-muted mb-4">Visitantes nao entram no ranking permanente.</p>
            @endif

            @if(isset($leaderboard) && $leaderboard->count())
            <div class="soft-panel text-start mb-4">
                <h5>Top 5 usuarios</h5>
                @foreach($leaderboard as $entry)
                    <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2 {{ session('user_id') == $entry->id ? 'bg-warning-subtle' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-badge :name="$entry->name" :rank="$entry->rank <= 3 ? $entry->rank : null" size="sm" />
                            <span>#{{ $entry->rank }} - {{ $entry->name }}</span>
                        </div>
                        <span>{{ $entry->total_score }} pts | {{ $entry->total_xp }} XP</span>
                    </div>
                @endforeach
            </div>
            @endif

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="/ranking" class="btn btn-outline-dark">
                    <i class="fas fa-medal me-2"></i>Ver ranking completo
                </a>
                <a href="/" class="btn btn-custom">Voltar ao inicio</a>
            </div>
        </div>
    </div>
</div>
@endsection
