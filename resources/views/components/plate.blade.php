@props(['nomor', 'size' => 'md'])

@php
    $ukuran = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-lg',
    ][$size] ?? 'px-3 py-1 text-sm';
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex rounded-md bg-neutral-900 p-[2px]'
]) }}>
    <span class="rounded-[4px] border border-white/70 font-bold uppercase tabular-nums tracking-[0.14em] text-white {{ $ukuran }}">
        {{ $nomor }}
    </span>
</span>