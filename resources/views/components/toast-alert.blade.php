@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
    $styles = [
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40',
            'border' => 'border-emerald-200 dark:border-emerald-800/60',
            'text' => 'text-emerald-800 dark:text-[#55C49A]',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            'btn' => 'text-emerald-500 hover:text-emerald-700',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-950/40',
            'border' => 'border-blue-200 dark:border-blue-800/60',
            'text' => 'text-blue-800 dark:text-[#7895FF]',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'btn' => 'text-blue-500 hover:text-blue-700',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/40',
            'border' => 'border-amber-200 dark:border-amber-800/60',
            'text' => 'text-amber-800 dark:text-[#E7B45B]',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            'btn' => 'text-amber-500 hover:text-amber-700',
        ],
        'danger' => [
            'bg' => 'bg-rose-50 dark:bg-rose-950/40',
            'border' => 'border-rose-200 dark:border-rose-800/60',
            'text' => 'text-rose-800 dark:text-[#F07878]',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'btn' => 'text-rose-500 hover:text-rose-700',
        ],
    ];

    $cfg = $styles[$type] ?? $styles['info'];
@endphp

<div class="p-3.5 rounded-xl border {{ $cfg['bg'] }} {{ $cfg['border'] }} {{ $cfg['text'] }} text-xs font-medium flex items-start justify-between gap-3 shadow-2xs transition-all">
    <div class="flex items-start gap-2.5">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $cfg['icon'] !!}
        </svg>
        <div class="space-y-0.5">
            @if($title)
                <div class="font-bold text-xs">{{ $title }}</div>
            @endif
            <div>{{ $slot }}</div>
        </div>
    </div>

    @if($dismissible)
        <button type="button" onclick="this.closest('.rounded-xl').remove()" class="{{ $cfg['btn'] }} transition font-bold text-base leading-none" title="Tutup">
            &times;
        </button>
    @endif
</div>
