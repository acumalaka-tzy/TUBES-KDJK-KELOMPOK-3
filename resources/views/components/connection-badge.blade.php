@props([
    'type' => 'direct',
    'dtls' => true,
    'e2ee' => true,
])

@php
    $configs = [
        'direct' => [
            'label' => 'P2P Direct',
            'dotColor' => 'bg-emerald-500',
            'textColor' => 'text-emerald-700 dark:text-[#55C49A]',
            'bgColor' => 'bg-emerald-50 dark:bg-emerald-950/40',
            'borderColor' => 'border-emerald-200 dark:border-emerald-800/60',
            'desc' => 'Koneksi langsung antar-peer tanpa relay perantara (Direct ICE)',
        ],
        'relay' => [
            'label' => 'TURN Relay',
            'dotColor' => 'bg-amber-500',
            'textColor' => 'text-amber-800 dark:text-[#E7B45B]',
            'bgColor' => 'bg-amber-50 dark:bg-amber-950/40',
            'borderColor' => 'border-amber-200 dark:border-amber-800/60',
            'desc' => 'Koneksi melalui relay TURN karena pembatasan NAT / Firewall',
        ],
        'connecting' => [
            'label' => 'Menghubungkan...',
            'dotColor' => 'bg-blue-500 animate-pulse',
            'textColor' => 'text-blue-700 dark:text-[#7895FF]',
            'bgColor' => 'bg-blue-50 dark:bg-blue-950/40',
            'borderColor' => 'border-blue-200 dark:border-blue-800/60',
            'desc' => 'Negosiasi SDP Offer/Answer dan kandidat ICE sedang berlangsung',
        ],
        'disconnected' => [
            'label' => 'Terputus',
            'dotColor' => 'bg-rose-500',
            'textColor' => 'text-rose-700 dark:text-[#F07878]',
            'bgColor' => 'bg-rose-50 dark:bg-rose-950/40',
            'borderColor' => 'border-rose-200 dark:border-rose-800/60',
            'desc' => 'Tidak ada koneksi aktif DataChannel dengan peer',
        ],
    ];

    $cfg = $configs[$type] ?? $configs['direct'];
@endphp

<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $cfg['bgColor'] }} {{ $cfg['borderColor'] }} {{ $cfg['textColor'] }} transition-all"
    title="{{ $cfg['desc'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dotColor'] }}"></span>
    <span>{{ $cfg['label'] }}</span>

    @if($dtls && $type === 'direct')
        <span class="text-[9px] px-1 py-0.2 rounded bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 font-mono" title="DTLS Transport Security">
            DTLS
        </span>
    @endif

    @if($e2ee && $type === 'direct')
        <span class="text-[9px] px-1 py-0.2 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 font-mono" title="End-to-End Encryption AES-GCM">
            E2EE
        </span>
    @endif
</div>
