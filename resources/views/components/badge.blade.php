@props(['type' => 'success'])

@php
$classes = match($type) {
    'success' => 'bg-primary/20 text-primary border-primary/20',
    'warning' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
    'danger' => 'bg-red-500/10 text-red-400 border-red-500/20',
    'info' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    default => 'bg-surface-highlight text-text-muted border-border-dark',
};

$icon = match($type) {
    'success' => 'check_circle',
    'warning' => 'hourglass_top',
    'danger' => 'cancel',
    'info' => 'info',
    default => 'circle',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border $classes"]) }}>
    <span class="material-symbols-outlined text-[14px]">{{ $icon }}</span>
    {{ $slot }}
</span>
