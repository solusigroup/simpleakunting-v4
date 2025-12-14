@props(['label', 'value', 'change' => null, 'changeType' => 'positive', 'icon' => null])

<div class="flex flex-col gap-3 rounded-2xl p-6 border border-border-dark bg-surface-dark/50">
    <div class="flex items-center justify-between">
        <p class="text-text-muted text-sm font-medium uppercase tracking-wider">{{ $label }}</p>
        @if($icon)
        <span class="material-symbols-outlined text-text-muted">{{ $icon }}</span>
        @endif
    </div>
    <p class="text-white text-2xl font-bold font-display">{{ $value }}</p>
    @if($change)
    <span class="inline-flex items-center gap-1 w-fit px-2 py-0.5 rounded text-xs font-bold
                {{ $changeType === 'positive' ? 'bg-accent-green/20 text-accent-green' : 'bg-accent-red/20 text-accent-red' }}">
        <span class="material-symbols-outlined text-[14px]">
            {{ $changeType === 'positive' ? 'trending_up' : 'trending_down' }}
        </span>
        {{ $change }}
    </span>
    @endif
</div>
