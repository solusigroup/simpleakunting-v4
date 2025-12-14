@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
          {{ $active 
             ? 'bg-surface-highlight text-white' 
             : 'text-text-muted hover:bg-surface-highlight hover:text-white' }}">
    <span class="material-symbols-outlined {{ $active ? 'text-primary' : '' }}">{{ $icon }}</span>
    <span class="text-sm font-medium">{{ $slot }}</span>
</a>
