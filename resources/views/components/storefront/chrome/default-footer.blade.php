@props(['footerSettings' => []])

@php
    use SoftLand\ThemeBuilder\Models\Theme;

    $bg = Theme::cssColor($footerSettings['background_color'] ?? '#0F172A', '#0F172A');
    $text = Theme::cssColor($footerSettings['text_color'] ?? '#F8FAFC', '#F8FAFC');
    $muted = Theme::cssColor($footerSettings['muted_color'] ?? '#94A3B8', '#94A3B8');
    $tagline = $footerSettings['tagline'] ?? null;
    $copyright = $footerSettings['copyright_text'] ?? config('app.name');
    $columns = $footerSettings['columns'] ?? [];
    $social = $footerSettings['social'] ?? [];
@endphp

<footer class="border-t border-line" style="background-color: {{ $bg }}; color: {{ $text }}">
    <div class="mx-auto grid max-w-content gap-10 px-4 py-12 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="space-y-3">
            <div class="font-display text-lg font-semibold">{{ config('app.name') }}</div>
            @if ($tagline)
                <p class="text-sm" style="color: {{ $muted }}">{{ tb_bi($tagline) }}</p>
            @endif
        </div>

        @foreach ($columns as $col)
            <div>
                <h4 class="mb-3 text-sm font-semibold">{{ tb_bi($col['heading'] ?? '') }}</h4>
                <ul class="space-y-2 text-sm" style="color: {{ $muted }}">
                    @foreach ($col['links'] ?? [] as $link)
                        <li><a href="{{ $link['url'] ?? '#' }}" class="hover:opacity-75">{{ tb_bi($link['label'] ?? '') }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="mx-auto flex max-w-content flex-col items-center justify-between gap-3 px-4 pb-8 text-xs sm:flex-row sm:px-6 lg:px-8" style="color: {{ $muted }}">
        <p>© {{ date('Y') }} {{ $copyright }}</p>
        @if (! empty($social))
            <div class="flex gap-3">
                @foreach ($social as $s)
                    <a href="{{ $s['url'] ?? '#' }}">{{ ucfirst((string) ($s['platform'] ?? '')) }}</a>
                @endforeach
            </div>
        @endif
    </div>
</footer>
