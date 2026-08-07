@props(['label' => null, 'error' => null])

<div class="space-y-1.5">
    @if ($label)
        <label class="block text-xs font-medium uppercase tracking-wide text-stone">{{ $label }}</label>
    @endif

    <textarea
        {{ $attributes->class([
            'block w-full rounded-(--radius-cadi) bg-surface ring-1 ring-line-strong',
            'px-3.5 py-2.5 text-sm text-ink placeholder:text-stone/60',
            'focus:outline-none focus:ring-2 focus:ring-clay-400',
        ]) }}
    >{{ $slot }}</textarea>

    @if ($error)
        <p class="text-sm text-clay-700">{{ $error }}</p>
    @endif
</div>
