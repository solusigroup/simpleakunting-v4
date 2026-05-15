@props(['type' => 'primary', 'href' => null, 'variant' => null])

@php
// Support both 'type' and 'variant' for styling to maintain backward compatibility
$style = $variant ?? $type;
$classes = match($style) {
    'primary' => 'bg-primary hover:bg-[#2ec56a] text-background-dark shadow-lg shadow-primary/20',
    'secondary' => 'border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white',
    'ghost' => 'text-text-muted hover:text-white hover:bg-surface-highlight',
    'danger' => 'bg-accent-red hover:bg-red-600 text-white shadow-lg shadow-accent-red/20',
    default => 'bg-primary hover:bg-[#2ec56a] text-background-dark',
};
$baseClasses = "inline-flex items-center justify-center gap-2 px-6 h-12 rounded-full font-bold transition-all duration-200 $classes";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $baseClasses, 'type' => $href ? null : 'submit']) }}>
        {{ $slot }}
    </button>
@endif
