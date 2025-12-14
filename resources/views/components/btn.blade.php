@props(['type' => 'primary'])

@php
$classes = match($type) {
    'primary' => 'bg-primary hover:bg-[#2ec56a] text-background-dark shadow-lg shadow-primary/20',
    'secondary' => 'border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white',
    'danger' => 'bg-accent-red hover:bg-red-600 text-white shadow-lg shadow-accent-red/20',
    default => 'bg-primary hover:bg-[#2ec56a] text-background-dark',
};
@endphp

<button {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 px-6 h-12 rounded-full font-bold transition-all duration-200 $classes"]) }}>
    {{ $slot }}
</button>
