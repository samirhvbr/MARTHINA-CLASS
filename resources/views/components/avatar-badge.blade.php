@props([
    'name',
    'rank' => null,
    'size' => 'md',
    'trophy' => null,
])

@php
    $trimmedName = trim((string) $name);
    $initial = $trimmedName !== ''
        ? (function_exists('mb_substr') ? mb_strtoupper(mb_substr($trimmedName, 0, 1)) : strtoupper(substr($trimmedName, 0, 1)))
        : '?';

    $sizeClass = match ($size) {
        'lg' => 'avatar-badge-lg',
        'sm' => 'avatar-badge-sm',
        default => 'avatar-badge-md',
    };

    $rankClass = match ((int) $rank) {
        1 => 'avatar-rank-gold',
        2 => 'avatar-rank-silver',
        3 => 'avatar-rank-bronze',
        default => 'avatar-rank-neutral',
    };

    $trophyIcon = $trophy ?: match ((int) $rank) {
        1 => 'crown',
        2 => 'medal',
        3 => 'award',
        default => 'star',
    };
@endphp

<div {{ $attributes->class(['avatar-badge', $sizeClass, $rankClass]) }}>
    <span>{{ $initial }}</span>
    @if($rank)
        <div class="avatar-trophy">
            <i class="fas fa-{{ $trophyIcon }}"></i>
        </div>
    @endif
</div>
