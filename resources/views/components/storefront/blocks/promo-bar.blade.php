@props(['props' => []])

@php
    $locale = app()->getLocale();
    $text = tb_bi($props['text']);
    $url = $props['url'] ?? null;
@endphp

@if ($url)
    <a href="{{ $url }}" class="block bg-clay-700 px-6 py-2.5 text-center text-caption font-medium text-paper transition-colors duration-(--duration-base) hover:bg-clay-800">
        {{ $text }}
    </a>
@else
    <div class="bg-clay-700 px-6 py-2.5 text-center text-caption font-medium text-paper">
        {{ $text }}
    </div>
@endif
