@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $url = $props['videoUrl'] ?? '';

    // Embed URL derivation: watch / embed / shorts / youtu.be / vimeo.
    $embed = null;
    if (preg_match('~(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/)|youtu\.be/)([\w-]+)~', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/'.$m[1];
    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/'.$m[1];
    }
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if ($embed)
    {{-- bg-ink, not a literal black: letterboxing should sit on the theme's ink. --}}
    <div class="aspect-video overflow-hidden rounded-card bg-ink">
        <iframe
            src="{{ $embed }}"
            class="h-full w-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy"
        ></iframe>
    </div>
@else
    <div class="flex aspect-video w-full items-center justify-center rounded-card border border-dashed border-line-strong bg-paper-deep text-body text-stone">
        {{ __('storefront.add_youtube_or_vimeo') }}
    </div>
@endif
