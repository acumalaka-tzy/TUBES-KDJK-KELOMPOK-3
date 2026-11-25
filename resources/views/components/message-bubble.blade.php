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
                <span class="sender-name font-semibold text-[#111B21] dark:text-[#E9EDEF]">{{ $senderName }}</span>
            @endif
            <span>{{ $timestamp }}</span>
            @if($isSelf)
                <span class="sender-name font-semibold text-[#111B21] dark:text-[#E9EDEF]">Anda</span>
            @endif
        </div>

        <!-- Bubble Container -->
        <div class="relative p-3.5 rounded-2xl {{ $isSelf ? 'rounded-tr-sm bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] border border-[#C5E8B7] dark:border-[#004D3F]' : 'rounded-tl-sm bg-white dark:bg-[#202C33] border border-[#E9EDEF] dark:border-[#222D34] text-[#111B21] dark:text-[#E9EDEF]' }} text-xs leading-relaxed shadow-xs">
            @if($replyAuthor && $replySnippet)
                <div class="mb-2 p-2 rounded-lg {{ $isSelf ? 'bg-[#C2E8BC]/80 dark:bg-[#004A3C]/80 text-[#111B21] dark:text-[#E9EDEF] border-l-2 border-[#00A884]' : 'bg-[#F0F2F5] dark:bg-[#111B21] border-l-2 border-[#00A884] text-[#111B21] dark:text-[#E9EDEF]' }} text-[11px]">
                    <div class="font-bold text-[#008069] dark:text-[#25D366]">{{ $replyAuthor }}</div>
                    <div class="truncate opacity-85">{{ $replySnippet }}</div>
                </div>
            @endif

            <div class="msg-content break-words whitespace-pre-wrap select-text">
                {{ $slot }}
            </div>
        </div>

        <!-- Status & Delivery Indicator -->
        <div class="flex items-center gap-1.5 text-[10px] text-[#667781] dark:text-[#8696A0] mt-1 font-mono">
            @if($isSelf)
                @if($status === 'sending')
                    <span class="text-slate-400">Sedang mengirim...</span>
                @elseif($status === 'sent')
                    <span class="text-slate-400 font-bold">✓ Sent</span>
                @elseif($status === 'delivered')
                    <span class="text-[#53BDEB] font-bold">✓✓ Delivered</span>
                @elseif($status === 'failed')
                    <span class="text-rose-500 font-bold">✕ Gagal</span>
                @endif
            @else
                <span class="text-[#25D366] font-bold">✓ E2EE</span>
            @endif

            @if($ephemeralDuration > 0)
                <span class="text-amber-500 font-bold">&bull; Ephemeral ({{ $ephemeralDuration }}s)</span>
            @endif
        </div>
    </div>
</div>
