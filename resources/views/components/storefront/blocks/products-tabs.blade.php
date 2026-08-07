@props(['props' => [], 'tabProducts' => []])

@php
    $locale = app()->getLocale();
    $columns = (int) ($props['columns'] ?? 4);
    $mobile = (int) ($props['mobileColumns'] ?? 2);
    $mobileClass = $mobile === 1 ? 'grid-cols-1' : 'grid-cols-2';
    $colClass = match (true) {
        $columns <= 1 => '',
        $columns === 2 => 'sm:grid-cols-2',
        $columns === 3 => 'sm:grid-cols-2 lg:grid-cols-3',
        $columns === 5 => 'sm:grid-cols-2 lg:grid-cols-5',
        $columns === 6 => 'sm:grid-cols-3 lg:grid-cols-6',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

<section x-data="{ activeTab: 0 }">
    <div class="mb-6 flex gap-2 border-b border-line">
        @foreach ($tabProducts as $i => $tab)
            <button
                type="button"
                @click="activeTab = {{ $i }}"
                :class="activeTab === {{ $i }} ? 'border-clay-600 text-clay-700' : 'border-transparent text-stone'"
                :aria-selected="activeTab === {{ $i }}"
                class="border-b-2 px-4 py-2 text-body font-medium transition-colors duration-(--duration-base) focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
            >
                {{ $tab['label'][$locale] ?? ($tab['label']['ar'] ?? '') }}
            </button>
        @endforeach
    </div>

    @foreach ($tabProducts as $i => $tab)
        <div x-show="activeTab === {{ $i }}" class="grid {{ $mobileClass }} gap-4 {{ $colClass }}">
            @foreach ($tab['products'] ?? [] as $product)
                <x-theme-builder::product.card :product="$product" />
            @endforeach
        </div>
    @endforeach
</section>
