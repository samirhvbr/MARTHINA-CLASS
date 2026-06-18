@props([
    'label',
    'theme' => 'neutral',
    'icon' => null,
])

@php
    $themeClass = match ($theme) {
        'english' => 'subject-theme-english',
        'portuguese' => 'subject-theme-portuguese',
        'math' => 'subject-theme-math',
        default => 'subject-theme-neutral',
    };
@endphp

<span {{ $attributes->class(['subject-pill', $themeClass]) }}>
    @if($icon)
        <i class="fas fa-{{ $icon }}"></i>
    @endif
    {{ $label }}
</span>
