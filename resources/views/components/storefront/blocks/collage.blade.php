@props(['props' => []])

@php
    $items = array_values(array_filter($props['items'] ?? [], fn ($i) => ! empty($i['image'])));
    $count = count($items);
    $zoom = '[&_img]:transition-transform [&_img]:duration-(--duration-base) [&_img]:ease-smooth group-hover:[&_img]:scale-105';
@endphp

@if ($count >= 2)
    @if ($count === 2)
        {{-- Two tall panels side by side. --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($items as $item)
                <a href="{{ $item['url'] ?: '#' }}" class="group block">
                    <x-theme-builder::ui.media :src="$item['image']" ratio="portrait" :class="$zoom" />
                </a>
            @endforeach
        </div>
    @elseif ($count === 3)
        {{-- One hero tile plus two stacked companions. --}}
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ $items[0]['url'] ?: '#' }}" class="group col-span-2 row-span-2 block">
                <x-theme-builder::ui.media :src="$items[0]['image']" ratio="square" :class="$zoom" />
            </a>
            @foreach (array_slice($items, 1) as $item)
                <a href="{{ $item['url'] ?: '#' }}" class="group block">
                    <x-theme-builder::ui.media :src="$item['image']" ratio="square" :class="$zoom" />
                </a>
            @endforeach
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach (array_slice($items, 0, 4) as $item)
                <a href="{{ $item['url'] ?: '#' }}" class="group block">
                    <x-theme-builder::ui.media :src="$item['image']" ratio="square" :class="$zoom" />
                </a>
            @endforeach
        </div>
    @endif
@endif
