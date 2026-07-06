@extends('layout')

@section('content')
@php
    $topThree = $leaderboard->take(3);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="mb-1"><i class="fas fa-medal text-warning me-2"></i>Ranking</h1>
                    <p class="text-muted mb-0">Pontuacao geral dos usuarios com base nos acertos e XP acumulado.</p>
                </div>
                <a href="/score" class="btn btn-outline-secondary">Voltar para meus pontos</a>
            </div>

            @if(isset($publicSubjectLegend) && $publicSubjectLegend->count())
                <div class="d-flex gap-2 flex-wrap mb-3">
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

            <div class="d-flex gap-2 flex-wrap mb-4">
                <x-difficulty-seal level="easy" />
                <x-difficulty-seal level="normal" />
                <x-difficulty-seal level="hard" />
            </div>

            @if($isGuest ?? false)
                <div class="alert alert-info">Voce esta em modo visitante. Seu progresso e temporario e nao aparece neste ranking.</div>
            @endif

            @if($topThree->count())
                <div class="row g-3 mb-4">
                    @foreach($topThree as $entry)
                        @php
                            $podiumClass = 'podium-bronze';

                            if ($entry->rank === 1) {
                                $podiumClass = 'podium-gold';
                            } elseif ($entry->rank === 2) {
                                $podiumClass = 'podium-silver';
                            }
                        @endphp
                        <div class="col-md-4">
                            <div class="card podium-card micro-float {{ $entry->rank === 2 ? 'micro-delay-2' : '' }} {{ $entry->rank === 3 ? 'micro-delay-3' : '' }} {{ $entry->id === $currentUserId ? 'border border-warning-subtle' : '' }}">
                                <div class="podium-rank {{ $podiumClass }} mx-auto">#{{ $entry->rank }}</div>
                                <x-avatar-badge :name="$entry->name" :rank="$entry->rank" size="lg" class="mx-auto mb-3" />
                                <div class="podium-trophy {{ $podiumClass }}">
                                    <i class="fas fa-{{ $entry->rank === 1 ? 'crown' : ($entry->rank === 2 ? 'medal' : 'award') }}"></i>
                                </div>
                                <h4 class="mb-3">{{ $entry->name }}</h4>
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="profile-quick-stat">
                                            <strong>{{ $entry->total_score }}</strong>
                                            <div class="small text-muted">Score</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="profile-quick-stat">
                                            <strong>{{ $entry->total_xp }}</strong>
                                            <div class="small text-muted">XP</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="profile-quick-stat">
                                            <strong>{{ $entry->total_correct }}</strong>
                                            <div class="small text-muted">Acertos</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Score</th>
                            <th>XP</th>
                            <th>Acertos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $entry)
                            <tr class="{{ $entry->id === $currentUserId ? 'table-warning' : '' }}">
                                <td class="fw-bold">{{ $entry->rank }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <x-avatar-badge :name="$entry->name" :rank="$entry->rank <= 3 ? $entry->rank : null" size="sm" />
                                        <div>
                                            <div class="fw-semibold">{{ $entry->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge text-bg-light">{{ $entry->total_score }}</span></td>
                                <td><span class="badge text-bg-light">{{ $entry->total_xp }}</span></td>
                                <td><span class="badge text-bg-light">{{ $entry->total_correct }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Ainda nao ha usuarios com pontuacao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
