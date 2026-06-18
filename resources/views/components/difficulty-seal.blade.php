@props([
    'label' => null,
    'level' => 'normal',
    'icon' => null,
])

@php
    $normalizedLevel = strtolower((string) $level);

    if ($label) {
        if (str_contains($label, 'Fac')) {
            $normalizedLevel = 'easy';
        } elseif (str_contains($label, 'Dif')) {
            $normalizedLevel = 'hard';
        }
    }

    $sealClass = match ($normalizedLevel) {
        'easy' => 'difficulty-easy',
        'hard' => 'difficulty-hard',
        default => 'difficulty-normal',
    };

    $sealIcon = $icon ?: match ($normalizedLevel) {
        'easy' => 'seedling',
        'hard' => 'fire',
        default => 'bolt',
    };

    $sealLabel = $label ?: match ($normalizedLevel) {
        'easy' => 'Facil',
        'hard' => 'Dificil',
        default => 'Normal',
    };
@endphp

<span {{ $attributes->class(['difficulty-seal', $sealClass]) }}>
    <i class="fas fa-{{ $sealIcon }}"></i>
    {{ $sealLabel }}
</span>
