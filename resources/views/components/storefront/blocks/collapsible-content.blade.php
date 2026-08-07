@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $items = $props['items'] ?? [];
@endphp

<x-theme-builder::storefront.section-heading :title="$title" />

@if (! empty($items))
    <div class="space-y-2">
        @foreach ($items as $item)
            @php
                $heading = tb_bi($item['heading']);
                $content = tb_bi($item['content']);
            @endphp
            <details class="group rounded-card border border-line bg-surface p-4 transition-colors hover:border-line-strong">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-body font-medium text-ink">
                    {{ $heading }}
                    <span class="text-stone transition-transform duration-(--duration-base) group-open:rotate-45" aria-hidden="true">+</span>
                </summary>
                <div class="rich-text mt-3 text-body text-ink-soft">{!! $content !!}</div>
            </details>
        @endforeach
    </div>
@endif
