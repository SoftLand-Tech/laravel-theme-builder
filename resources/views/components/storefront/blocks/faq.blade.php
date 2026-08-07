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
                $question = tb_bi($item['question']);
                $answer = tb_bi($item['answer']);
            @endphp
            <details class="group rounded-card border border-line bg-surface p-4 transition-colors hover:border-line-strong">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-body font-medium text-ink">
                    {{ $question }}
                    <span class="text-stone transition-transform duration-(--duration-base) group-open:rotate-45" aria-hidden="true">+</span>
                </summary>
                <p class="mt-3 text-body text-ink-soft">{!! nl2br(e($answer)) !!}</p>
            </details>
        @endforeach
    </div>
@else
    <p class="text-body text-stone">{{ __('storefront.no_faqs') }}</p>
@endif
