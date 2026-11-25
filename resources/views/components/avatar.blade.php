@props([
    'name' => 'User',
    'color' => '#315CE8',
    'size' => 'md',
    'online' => false,
])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-7 h-7 text-xs',
        'md' => 'w-8 h-8 text-xs',
        'lg' => 'w-10 h-10 text-sm',
        'xl' => 'w-14 h-14 text-xl',
    ];

    $badgeSizes = [
        'xs' => 'w-1.5 h-1.5',
        'sm' => 'w-2 h-2',
        'md' => 'w-2.5 h-2.5',
        'lg' => 'w-3 h-3',
        'xl' => 'w-3.5 h-3.5',
    ];

    $selectedSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $selectedBadge = $badgeSizes[$size] ?? $badgeSizes['md'];
    $initial = strtoupper(substr(trim($name), 0, 1) ?: 'U');
@endphp

<div class="relative inline-flex shrink-0">
    <div class="{{ $selectedSize }} rounded-full flex items-center justify-center text-white font-bold uppercase shadow-2xs select-none"
        style="background-color: {{ $color }};"
        title="{{ $name }}">
        {{ $initial }}
    </div>

    @if($online)
        <span class="absolute bottom-0 right-0 {{ $selectedBadge }} rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#1B2230]"
            title="Online"></span>
    @endif
</div>
