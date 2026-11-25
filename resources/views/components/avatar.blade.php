@props([
    'name' => 'User',
    'color' => '#008069',
    'icon' => null,
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
        '2xl' => 'w-20 h-20 text-3xl',
    ];

    $badgeSizes = [
        'xs' => 'w-1.5 h-1.5',
        'sm' => 'w-2 h-2',
        'md' => 'w-2.5 h-2.5',
        'lg' => 'w-3 h-3',
        'xl' => 'w-3.5 h-3.5',
        '2xl' => 'w-4 h-4',
    ];

    $selectedSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $selectedBadge = $badgeSizes[$size] ?? $badgeSizes['md'];

    $resolvedKey = \App\Models\UserSession::$legacyAliases[$icon] ?? $icon;
    $hasSvg = (!empty($resolvedKey) && $resolvedKey !== 'initial' && isset(\App\Models\UserSession::$avatarSvgs[$resolvedKey]));
    $svgContent = $hasSvg ? \App\Models\UserSession::$avatarSvgs[$resolvedKey] : null;
    $initial = strtoupper(substr(trim($name), 0, 1) ?: 'U');
@endphp

<div class="relative inline-flex shrink-0">
    <div class="{{ $selectedSize }} rounded-full flex items-center justify-center text-white font-bold uppercase shadow-2xs select-none overflow-hidden"
        style="background-color: {{ $color }};"
        title="{{ $name }}">
        @if($hasSvg)
            <div class="w-full h-full flex items-center justify-center p-0.5 pointer-events-none">
                {!! $svgContent !!}
            </div>
        @else
            <span class="font-bold select-none leading-none">{{ $initial }}</span>
        @endif
    </div>

    @if($online)
        <span class="absolute bottom-0 right-0 {{ $selectedBadge }} rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#111B21]"
            title="Online"></span>
    @endif
</div>
