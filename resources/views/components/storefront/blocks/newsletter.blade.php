@props(['props' => []])

@php
    $title = tb_bi($props['title'] ?? null);
    $subtitle = tb_bi($props['subtitle'] ?? null);
    $placeholder = tb_bi($props['placeholder'] ?? null);
    $ctaText = tb_bi($props['ctaText'] ?? null);
    $layout = $props['layout'] ?? 'center';

    $align = match ($layout) {
        'start', 'left' => 'items-start text-start',
        'end', 'right' => 'items-end text-end',
        default => 'items-center text-center',
    };
@endphp

<div class="flex flex-col gap-4 {{ $align }}">
    @if ($title)
        <h2 class="font-display text-h2 text-ink">{{ $title }}</h2>
    @endif

    @if ($subtitle)
        <p class="max-w-xl text-lead text-ink-soft">{{ $subtitle }}</p>
    @endif

    <form class="flex w-full max-w-md flex-col gap-2 sm:flex-row" action="#" method="post">
        @csrf
        <input
            type="email"
            name="email"
            placeholder="{{ $placeholder }}"
            required
            class="w-full rounded-(--radius-button) border border-line bg-paper px-4 py-2.5 text-body text-ink outline-clay-500"
        >
        <button
            type="submit"
            class="shrink-0 rounded-(--radius-button) bg-clay-600 px-5 py-2.5 font-semibold text-paper transition hover:bg-clay-700"
        >
            {{ $ctaText }}
        </button>
    </form>
</div>
