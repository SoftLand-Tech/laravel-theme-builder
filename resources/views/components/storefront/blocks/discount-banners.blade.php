@props(['props' => []])

@php
    $panels = [
        [
            'meta' => tb_bi($props['leftMeta'] ?? null),
            'title' => tb_bi($props['leftTitle'] ?? null),
            'pct' => tb_bi($props['leftPct'] ?? null),
            'cta' => tb_bi($props['leftCta'] ?? null),
            'image' => $props['leftImage'] ?? null,
            'url' => $props['leftUrl'] ?: '#',
        ],
        [
            'meta' => tb_bi($props['rightMeta'] ?? null),
            'title' => tb_bi($props['rightTitle'] ?? null),
            'pct' => tb_bi($props['rightPct'] ?? null),
            'cta' => tb_bi($props['rightCta'] ?? null),
            'image' => $props['rightImage'] ?? null,
            'url' => $props['rightUrl'] ?: '#',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @foreach ($panels as $panel)
        <a href="{{ $panel['url'] }}" class="group relative flex flex-col justify-between overflow-hidden rounded-(--radius-card) bg-ink p-6 text-paper min-h-[200px]">
            <div class="relative z-10">
                @if ($panel['meta'])
                    <span class="text-xs font-semibold uppercase tracking-wide text-clay-300">{{ $panel['meta'] }}</span>
                @endif
                @if ($panel['title'])
                    <h3 class="mt-2 font-display text-h3 font-bold">
                        {{ $panel['title'] }}
                        @if ($panel['pct'])
                            <br><span class="text-clay-400">{{ $panel['pct'] }}</span>
                        @endif
                    </h3>
                @endif
                @if ($panel['cta'])
                    <span class="mt-3 inline-block text-sm font-semibold underline-offset-4 group-hover:underline">{{ $panel['cta'] }} →</span>
                @endif
            </div>
            @if ($panel['image'])
                <img src="{{ $panel['image'] }}" alt="" class="pointer-events-none absolute bottom-3 right-3 z-0 h-24 w-24 rounded-lg object-cover opacity-90 transition-transform duration-(--duration-base) ease-smooth group-hover:scale-105">
            @endif
        </a>
    @endforeach
</div>
