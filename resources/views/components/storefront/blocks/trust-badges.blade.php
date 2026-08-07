@props(['props' => []])

@php
    $locale = app()->getLocale();
    $items = $props['items'] ?? [];
@endphp

<div class="grid grid-cols-2 gap-3 @md:grid-cols-4">
    @foreach ($items as $item)
        @php
            $label = tb_bi($item['label']);
            $icon = $item['icon'] ?? 'check';
        @endphp
        <div class="flex items-center justify-center gap-2 rounded-card border border-line bg-surface px-4 py-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 text-clay-600" aria-hidden="true">
                <path d="{{ \SoftLand\ThemeBuilder\Support\IconSvg::path($icon) }}"></path>
            </svg>
            <span class="text-caption text-ink-soft">{{ $label }}</span>
        </div>
    @endforeach
</div>
