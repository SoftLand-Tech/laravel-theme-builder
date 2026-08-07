@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
])

@php
    // `$errors` is shared by ShareErrorsFromSession on web requests, but not when a
    // view is rendered outside the web middleware group.
    $fieldErrors = $errors ?? new \Illuminate\Support\ViewErrorBag;
@endphp

{{--
    The control is caller-supplied, so this component cannot put attributes on it.
    Callers must give the control `id="{{ $name }}"` (to match the label's `for`)
    and, so screen readers announce the message, `aria-describedby="{{ $name }}-error"`.
--}}
<div {{ $attributes->class(['space-y-1.5 text-start']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-caption font-medium text-ink">
            {{ $label }}
            @if ($required)
                <span class="text-sale" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint && ! $fieldErrors->has($name))
        <p class="text-caption text-stone">{{ $hint }}</p>
    @endif

    @if ($fieldErrors->has($name))
        <p id="{{ $name }}-error" role="alert" class="text-caption text-sale">{{ $fieldErrors->first($name) }}</p>
    @endif
</div>
