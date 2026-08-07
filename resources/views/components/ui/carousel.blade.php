@props(['label' => null, 'autoplay' => false, 'interval' => 5])

<div
    x-data="{
        step() {
            return this.$refs.track.clientWidth * 0.8;
        },
        sign() {
            return getComputedStyle(this.$refs.track).direction === 'rtl' ? -1 : 1;
        },
        next() { this.$refs.track.scrollBy({ left: this.sign() * this.step(), behavior: 'smooth' }); },
        prev() { this.$refs.track.scrollBy({ left: -this.sign() * this.step(), behavior: 'smooth' }); },
        timer: null,
        init() {
            if (@json((bool) $autoplay)) {
                const ms = {{ max(3, (int) $interval) * 1000 }};
                this.timer = setInterval(() => this.next(), ms);
            }
        },
        destroy() {
            if (this.timer) {
                clearInterval(this.timer);
            }
        },
    }"
    {{ $attributes->class(['relative']) }}
    @if ($label) role="group" aria-label="{{ $label }}" @endif
>
    <div class="mb-3 flex justify-end gap-2">
        <button
            type="button"
            @click="prev()"
            class="grid h-9 w-9 place-items-center rounded-(--radius-pill) ring-1 ring-line-strong text-ink transition hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
            aria-label="Previous"
        >
            <span class="rtl:hidden" aria-hidden="true">&larr;</span>
            <span class="ltr:hidden" aria-hidden="true">&rarr;</span>
        </button>
        <button
            type="button"
            @click="next()"
            class="grid h-9 w-9 place-items-center rounded-(--radius-pill) ring-1 ring-line-strong text-ink transition hover:bg-paper-deep focus-visible:outline-(length:--focus-ring-width) focus-visible:outline-(--focus-ring-color)"
            aria-label="Next"
        >
            <span class="rtl:hidden" aria-hidden="true">&rarr;</span>
            <span class="ltr:hidden" aria-hidden="true">&larr;</span>
        </button>
    </div>

    <div
        x-ref="track"
        class="flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-ps-4 scroll-pe-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
        {{ $slot }}
    </div>
</div>
