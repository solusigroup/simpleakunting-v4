# Simple Akunting 4.0 - Style Guide

Style guide berdasarkan desain Stitch yang sudah di-export.

---

## Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `primary` | `#36e27b` | Buttons, highlights, active states |
| `accent-green` | `#0bda43` | Positive indicators (+%) |
| `accent-red` | `#fa5538` | Negative indicators, errors |
| `background-dark` | `#112117` / `#122118` | Main background |
| `surface-dark` | `#1a2e22` | Cards, sidebar |
| `surface-highlight` | `#254632` | Hover states, active items |
| `primary-dark` | `#254632` | Secondary buttons |
| `text-muted` | `#95c6a9` | Secondary text |
| `border` | `#366348` | Card borders |

---

## Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| Display/Headers | `Inter` or `Spline Sans` | 700-900 | 3xl-4xl |
| Body | `Noto Sans` | 400-500 | sm-base |
| Monospace (numbers) | `font-mono` | 400 | - |

---

## Components

### Sidebar Navigation
```html
<aside class="w-72 h-screen sticky top-0 border-r border-surface-highlight bg-background-dark p-6">
  <nav class="flex flex-col gap-2">
    <!-- Active item -->
    <a class="flex items-center gap-3 px-4 py-3 rounded-xl bg-surface-highlight">
      <span class="material-symbols-outlined text-primary">icon</span>
      <p class="text-white text-sm font-bold">Active Menu</p>
    </a>
    <!-- Inactive item -->
    <a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-highlight">
      <span class="material-symbols-outlined text-text-secondary">icon</span>
      <p class="text-text-secondary text-sm font-medium">Menu Item</p>
    </a>
  </nav>
</aside>
```

### Stat Cards
```html
<div class="flex flex-col gap-3 rounded-2xl p-6 border border-[#366348] bg-[#1a2e22]/50">
  <p class="text-text-muted text-sm font-medium uppercase tracking-wider">Label</p>
  <p class="text-white text-2xl font-bold">Rp 42.500.000</p>
  <span class="bg-accent-green/20 text-accent-green text-xs font-bold px-2 py-0.5 rounded">
    +12%
  </span>
</div>
```

### Primary Button
```html
<button class="flex items-center gap-2 h-12 px-6 rounded-full bg-primary hover:bg-[#2ec56a] text-background-dark font-bold shadow-lg shadow-primary/20">
  <span class="material-symbols-outlined">add</span>
  Button Text
</button>
```

### Secondary Button
```html
<button class="flex items-center gap-2 h-10 px-4 rounded-full border border-primary-dark text-text-muted hover:bg-primary-dark hover:text-white">
  <span class="material-symbols-outlined">icon</span>
  Button Text
</button>
```

### Data Table
```html
<div class="rounded-2xl border border-[#366348] overflow-hidden bg-[#1a2e22]/30">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr class="border-b border-[#366348] bg-[#1a2e22]">
        <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Column</th>
      </tr>
    </thead>
    <tbody class="text-sm">
      <tr class="border-b border-[#366348]/50 hover:bg-[#254632]/50">
        <td class="p-4 text-white">Cell content</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Status Badges
```html
<!-- Success -->
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/20 text-primary text-xs font-bold border border-primary/20">
  <span class="material-symbols-outlined text-[14px]">check_circle</span> Selesai
</span>
<!-- Warning -->
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-orange-500/10 text-orange-400 text-xs font-bold">
  <span class="material-symbols-outlined text-[14px]">hourglass_top</span> Menunggu
</span>
<!-- Error -->
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-500/10 text-red-400 text-xs font-bold">
  <span class="material-symbols-outlined text-[14px]">cancel</span> Dibatalkan
</span>
```

---

## Icons
Using **Material Symbols Outlined** from Google Fonts:
```html
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />
<span class="material-symbols-outlined">icon_name</span>
```

---

## Tailwind Config
```javascript
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary": "#36e27b",
        "primary-dark": "#254632",
        "accent-green": "#0bda43",
        "accent-red": "#fa5538",
        "text-muted": "#95c6a9",
        "text-secondary": "#95c6a9",
        "background-light": "#f6f8f7",
        "background-dark": "#112117",
        "surface-dark": "#1a2e22",
        "surface-highlight": "#254632",
      },
      fontFamily: {
        "display": ["Inter", "Spline Sans", "sans-serif"],
        "body": ["Noto Sans", "sans-serif"],
      },
      borderRadius: {
        "DEFAULT": "1rem",
        "lg": "2rem",
        "xl": "3rem",
        "full": "9999px"
      },
    },
  },
}
```
