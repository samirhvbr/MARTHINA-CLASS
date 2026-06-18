@props([
    'value',
    'label',
    'variant' => 'blue',
    'icon' => null,
])

@php
    $variantClass = match ($variant) {
        'pink' => 'feature-stat-pink',
        'green' => 'feature-stat-green',
        'gold' => 'feature-stat-gold',
        'silver' => 'feature-stat-silver',
        'bronze' => 'feature-stat-bronze',
        default => 'feature-stat-blue',
    };
@endphp

<div {{ $attributes->class(['feature-stat', $variantClass]) }}>
    @if($icon)
        <div class="feature-stat-icon">
            <i class="fas fa-{{ $icon }}"></i>
        </div>
    @endif
    <h4 class="mb-1">{{ $value }}</h4>
    <p class="mb-0">{{ $label }}</p>
</div>
