@props(['props' => []])

@php
    $locale = app()->getLocale();
    $panels = [
        [
            'image' => $props['leftImage'] ?? null,
            'title' => $props['leftTitle'][$locale] ?? ($props['leftTitle']['ar'] ?? ''),
            'url' => $props['leftUrl'] ?: '#',
        ],
        [
            'image' => $props['rightImage'] ?? null,
            'title' => $props['rightTitle'][$locale] ?? ($props['rightTitle']['ar'] ?? ''),
            'url' => $props['rightUrl'] ?: '#',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @foreach ($panels as $panel)
        <a href="{{ $panel['url'] }}" class="group block">
            <x-theme-builder::ui.media
                :src="$panel['image']"
                ratio="landscape"
                class="min-h-[220px] [&_img]:transition-transform [&_img]:duration-(--duration-base) [&_img]:ease-smooth group-hover:[&_img]:scale-105"
            >
                <div class="absolute inset-0 flex items-end bg-gradient-to-t from-ink/70 via-ink/20 to-transparent p-6">
                    @if ($panel['title'])
                        <h3 class="font-display text-h3 text-paper">{{ $panel['title'] }}</h3>
                    @endif
                </div>
            </x-theme-builder::ui.media>
        </a>
    @endforeach
</div>
