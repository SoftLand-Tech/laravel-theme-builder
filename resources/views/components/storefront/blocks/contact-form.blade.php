@props(['props' => []])

@php
    $locale = app()->getLocale();
    $title = tb_bi($props['title']);
    $subtitle = tb_bi($props['subtitle']);
    $showPhone = $props['showPhone'] ?? true;
    $success = session('contact_success', false);
@endphp

@if ($success)
    {{-- The 50/200/700 steps of the success ramp are not declared in @theme, so
         an alpha modifier on success-600 is used instead of `bg-success-50`. --}}
    <div role="status" class="mb-4 rounded-card border border-success-600/30 bg-success-600/10 px-4 py-3 text-body text-success-600">
        {{ __('storefront.contact_success') }}
    </div>
@endif

<x-theme-builder::storefront.section-heading :title="$title" :subtitle="$subtitle" />

<form action="{{ tb_route('storefront.contact.store') }}" method="POST" class="max-w-prose space-y-4">
    @csrf

    <x-theme-builder::ui.field name="name" :label="__('storefront.contact_name')" required>
        <x-theme-builder::ui.input id="name" type="text" name="name" required :value="old('name')" aria-describedby="name-error" />
    </x-theme-builder::ui.field>

    <x-theme-builder::ui.field name="email" :label="__('storefront.contact_email')" required>
        <x-theme-builder::ui.input id="email" type="email" name="email" required :value="old('email')" aria-describedby="email-error" />
    </x-theme-builder::ui.field>

    @if ($showPhone)
        <x-theme-builder::ui.field name="phone" :label="__('storefront.contact_phone')">
            <x-theme-builder::ui.input id="phone" type="tel" name="phone" :value="old('phone')" placeholder="+966 5XXXXXXXX" aria-describedby="phone-error" />
        </x-theme-builder::ui.field>
    @endif

    <x-theme-builder::ui.field name="message" :label="__('storefront.contact_message')" required>
        <x-theme-builder::ui.textarea id="message" name="message" rows="4" required aria-describedby="message-error">{{ old('message') }}</x-theme-builder::ui.textarea>
    </x-theme-builder::ui.field>

    <x-theme-builder::ui.button type="submit">{{ __('storefront.contact_send') }}</x-theme-builder::ui.button>
</form>
