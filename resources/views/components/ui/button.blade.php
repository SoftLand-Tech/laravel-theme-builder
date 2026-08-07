@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-clay-600 hover:bg-clay-700 text-paper',
        // The merchant's header brand accent (exposed at :root by the layout),
        // for surfaces that should match the store's branding rather than the
        // derived primary — e.g. the auth modal.
        'brand' => 'bg-[color:var(--header-accent)] text-paper hover:brightness-95',
        'secondary' => 'bg-ink hover:bg-ink-soft text-paper',
        'outline' => 'bg-transparent hover:bg-paper-deep text-ink ring-1 ring-line-strong',
        'ghost' => 'bg-transparent hover:bg-paper-deep text-ink-soft',
        'danger' => 'bg-danger-600 hover:bg-danger-500 text-paper',
    ];

    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-7 py-3 text-sm',
    ];

    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-(--radius-cadi) font-medium',
        'transition-colors duration-150 focus:outline-none focus-visible:ring-2',
        'focus-visible:ring-clay-400 focus-visible:ring-offset-2 focus-visible:ring-offset-paper',
        'disabled:opacity-50 disabled:pointer-events-none cursor-pointer',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
