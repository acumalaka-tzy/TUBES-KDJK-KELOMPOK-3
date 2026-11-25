@props([
    'isSelf' => false,
    'senderName' => 'Alice',
    'senderColor' => '#315CE8',
    'timestamp' => '12:00',
    'status' => 'delivered',
    'ephemeralDuration' => 0,
    'replyAuthor' => null,
    'replySnippet' => null,
])

<div class="flex items-start {{ $isSelf ? 'justify-end' : 'justify-start' }} gap-2.5 group">
    @if(!$isSelf)
        <x-avatar :name="$senderName" :color="$senderColor" size="sm" />
    @endif

    <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }} max-w-[85%] sm:max-w-[70%]">
        <!-- Metadata Header -->
        <div class="flex items-center gap-1.5 text-[11px] text-[#667085] dark:text-[#A8B2C3] mb-1">
            @if(!$isSelf)
                <span class="font-semibold text-[#182033] dark:text-[#F1F4FA]">{{ $senderName }}</span>
            @endif
            <span>{{ $timestamp }}</span>
            @if($isSelf)
                <span class="font-semibold text-[#182033] dark:text-[#F1F4FA]">Anda</span>
            @endif
        </div>

        <!-- Bubble Container -->
        <div class="relative p-3.5 rounded-2xl {{ $isSelf ? 'rounded-tr-sm bg-[#315CE8] text-white' : 'rounded-tl-sm bg-[#EEF1F7] dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-[#182033] dark:text-[#F1F4FA]' }} text-xs leading-relaxed shadow-2xs">
            @if($replyAuthor && $replySnippet)
                <div class="mb-2 p-2 rounded-lg {{ $isSelf ? 'bg-blue-700/60 text-white/90 border-l-2 border-white' : 'bg-white/60 dark:bg-[#151A24]/60 border-l-2 border-[#315CE8] text-[#182033] dark:text-[#F1F4FA]' }} text-[11px]">
                    <div class="font-bold">{{ $replyAuthor }}</div>
                    <div class="truncate opacity-80">{{ $replySnippet }}</div>
                </div>
            @endif

            <div>
                {{ $slot }}
            </div>
        </div>

        <!-- Status & Delivery Indicator -->
        <div class="flex items-center gap-1.5 text-[10px] text-[#667085] dark:text-[#A8B2C3] mt-1 font-mono">
            @if($isSelf)
                @if($status === 'sending')
                    <span class="text-slate-400">Sedang mengirim...</span>
                @elseif($status === 'sent')
                    <span class="text-blue-500 font-bold">✓ Sent</span>
                @elseif($status === 'delivered')
                    <span class="text-[#315CE8] dark:text-[#7895FF] font-bold">✓✓ Delivered</span>
                @elseif($status === 'failed')
                    <span class="text-rose-500 font-bold">✕ Gagal</span>
                @endif
            @else
                <span class="text-emerald-600 dark:text-[#55C49A] font-bold">✓ E2EE</span>
            @endif

            @if($ephemeralDuration > 0)
                <span class="text-amber-500 font-bold">&bull; Ephemeral ({{ $ephemeralDuration }}s)</span>
            @endif
        </div>
    </div>
</div>
