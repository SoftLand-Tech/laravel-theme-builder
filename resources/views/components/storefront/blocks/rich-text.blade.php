@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $content = tb_bi($props['content']);
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

{{-- max-w-prose keeps a readable measure without re-declaring a page container. --}}
<div class="rich-text max-w-prose text-body text-ink-soft">
    {!! $content !!}
</div>
