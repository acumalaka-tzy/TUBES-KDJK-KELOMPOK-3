@extends('layouts.app')

@section('title', $room->room_name . ' — SecureLink Chat')

@section('content')
<div class="h-[calc(100vh-8.5rem)] min-h-[580px] flex rounded-3xl glass-card overflow-hidden relative shadow-xl">

    <!-- 1. LEFT CONVERSATION SIDEBAR (Desktop always visible, Mobile toggleable) -->
    <aside id="chat-sidebar" class="w-full md:w-80 border-r border-[#E3E7EF] dark:border-[#344052] bg-[#F4F6FA]/60 dark:bg-[#151A24]/60 flex flex-col shrink-0 md:flex hidden">
        <!-- Sidebar Header: Search & Action -->
        <div class="p-3.5 border-b border-[#E3E7EF] dark:border-[#344052] space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#182033] dark:text-[#F1F4FA] uppercase tracking-wider">Percakapan</span>
                <div class="flex items-center gap-1">
                    <a href="{{ route('rooms.create') }}" class="p-1 rounded-lg text-[#315CE8] dark:text-[#7895FF] hover:bg-blue-50 dark:hover:bg-blue-950/40 transition" title="Buat Room Baru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                    <a href="{{ route('rooms.join') }}" class="p-1 rounded-lg text-[#667085] dark:text-[#A8B2C3] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] transition" title="Gabung Room">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>
            <input type="text" id="sidebar-search" placeholder="Cari obrolan..." oninput="filterSidebarRooms()"
                class="w-full px-3 py-1.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#1B2230] text-xs text-[#182033] dark:text-[#F1F4FA] placeholder-[#667085] dark:placeholder-[#A8B2C3]/60 focus:outline-none focus:ring-1 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
        </div>

        <!-- Room Items List -->
        <div id="sidebar-rooms-list" class="flex-1 overflow-y-auto p-2 space-y-1">
            @if(isset($userRooms) && $userRooms->isNotEmpty())
                @foreach($userRooms as $uPart)
                    @php $uRoom = $uPart->room; $isActive = ($uRoom->room_id === $room->room_id); @endphp
                    <a href="{{ route('rooms.show', $uRoom->room_id) }}"
                        class="sidebar-item block p-2.5 rounded-xl transition text-left {{ $isActive ? 'bg-[#315CE8]/10 dark:bg-[#7895FF]/15 border border-[#315CE8]/30 dark:border-[#7895FF]/30' : 'hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] border border-transparent' }}"
                        data-name="{{ strtolower($uRoom->room_name) }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold truncate {{ $isActive ? 'text-[#315CE8] dark:text-[#7895FF]' : 'text-[#182033] dark:text-[#F1F4FA]' }}">
                                {{ $uRoom->room_name }}
                            </span>
                            @if($isActive)
                                <span class="w-2 h-2 rounded-full bg-[#315CE8] dark:bg-[#7895FF] shrink-0"></span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1 font-mono">
                            <span>{{ $uRoom->room_id }}</span>
                            <span>{{ $uRoom->active_participants_count ?? 1 }} peer</span>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="p-3 text-center text-xs text-[#667085] dark:text-[#A8B2C3]">
                    Room aktif: <strong>{{ $room->room_name }}</strong>
                </div>
            @endif
        </div>

        <!-- Sidebar User Footer -->
        <div class="p-3 border-t border-[#E3E7EF] dark:border-[#344052] bg-white/40 dark:bg-[#1B2230]/40 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 uppercase" style="background-color: {{ $user->avatar_color }}">
                    {{ substr($user->display_name, 0, 1) }}
                </div>
                <div class="truncate">
                    <div class="text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] truncate">{{ $user->display_name }}</div>
                    <div class="text-[10px] text-emerald-600 dark:text-[#55C49A] font-mono leading-none">● Online</div>
                </div>
            </div>
            <a href="{{ route('settings') }}" class="p-1.5 rounded-lg text-[#667085] dark:text-[#A8B2C3] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] transition" title="Pengaturan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
        </div>
    </aside>

    <!-- 2. RIGHT ACTIVE CHAT AREA -->
    <main class="flex-1 flex flex-col min-w-0 bg-white dark:bg-[#1B2230] relative">

        <!-- Active Chat Header -->
        <header class="px-4 sm:px-6 py-3.5 border-b border-[#E3E7EF] dark:border-[#344052] bg-white/90 dark:bg-[#1B2230]/90 backdrop-blur-md flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Mobile Back Button -->
                <a href="{{ route('dashboard') }}" class="md:hidden p-1.5 rounded-lg text-[#667085] dark:text-[#A8B2C3] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] transition" title="Kembali ke Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-extrabold text-[#182033] dark:text-[#F1F4FA] truncate leading-tight">
                            {{ $room->room_name }}
                        </h2>
                        @if($room->isLocked())
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-[#F07878] font-bold shrink-0">
                                Terkunci
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-xs font-mono text-[#667085] dark:text-[#A8B2C3] mt-0.5">
                        <span class="truncate">ID: {{ $room->room_id }}</span>
                        <button type="button" onclick="copyRoomId('{{ $room->room_id }}')" class="text-[#315CE8] dark:text-[#7895FF] hover:underline text-[11px] font-sans" title="Salin ID">Salin</button>
                        <span>&bull;</span>
                        <span id="header-conn-status" class="text-emerald-600 dark:text-[#55C49A] font-semibold font-sans">
                            ● P2P Direct
                        </span>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Dynamic PIN Display for Creator -->
                @if($metadata['is_creator'] && !empty($metadata['current_dynamic_pin']))
                    <div class="hidden lg:flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs">
                        <span class="text-amber-800 dark:text-amber-300 font-medium">TOTP:</span>
                        <span class="font-mono font-bold text-amber-900 dark:text-amber-200 tracking-wider">{{ $metadata['current_dynamic_pin'] }}</span>
                    </div>
                @endif

                <!-- QR Invitation Button -->
                <button type="button" onclick="openInviteModal()" class="px-3 py-1.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] transition flex items-center gap-1.5" title="Undang Peserta">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span class="hidden sm:inline">Undang</span>
                </button>

                <!-- Toggle Members Drawer -->
                <button type="button" onclick="toggleMembersDrawer()" class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-[#315CE8] dark:text-[#7895FF] border border-blue-200 dark:border-blue-800/60 text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span id="members-count">{{ count($metadata['participants']) }}</span> <span class="hidden sm:inline">Peserta</span>
                </button>

                <!-- Leave Room -->
                <form action="{{ route('rooms.leave.web', $room->room_id) }}" method="POST" onsubmit="return confirm('Keluar dari room ini?')">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Keluar Room">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Message History Container -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-[#EFEAE2]/60 dark:bg-[#0B141A]">
            <!-- System Security Badge -->
            <div class="text-center my-1">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#EEF1F7]/80 dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-[#55C49A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>P2P WebRTC DataChannel aktif &bull; Enkripsi AES-GCM &bull; Server tidak menyimpan obrolan</span>
                </div>
            </div>

            <!-- Dynamic Messages List -->
            <div id="messages-list" class="space-y-4">
                <!-- System Initial Welcome Bubble -->
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#315CE8] text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-2xs">
                        S
                    </div>
                    <div class="flex flex-col items-start max-w-[85%] sm:max-w-[70%]">
                        <div class="flex items-center gap-1.5 text-[11px] text-[#667085] dark:text-[#A8B2C3] mb-1">
                            <span class="font-semibold text-[#182033] dark:text-[#F1F4FA]">SecureLink System</span>
                            <span>{{ now()->format('H:i') }}</span>
                        </div>
                        <div class="p-3.5 rounded-2xl rounded-tl-sm bg-[#EEF1F7] dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-xs text-[#182033] dark:text-[#F1F4FA] leading-relaxed shadow-2xs">
                            Selamat datang di room <strong>{{ $room->room_name }}</strong>. Koneksi pesan langsung diamankan secara bilateral antarperangkat.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Preview Bar (Populated on Reply click) -->
        <div id="reply-bar" class="hidden px-4 py-2 bg-blue-50/70 dark:bg-blue-950/30 border-t border-[#E3E7EF] dark:border-[#344052] flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-[#315CE8] dark:text-[#7895FF] font-bold">Membalas:</span>
                <span id="reply-author" class="font-semibold text-[#182033] dark:text-[#F1F4FA]"></span>
                <span id="reply-text" class="text-[#667085] dark:text-[#A8B2C3] truncate max-w-xs"></span>
            </div>
            <button type="button" onclick="cancelReply()" class="text-[#667085] hover:text-[#182033] dark:hover:text-[#F1F4FA] font-bold text-sm">
                &times;
            </button>
        </div>

        <!-- 3. MESSAGE COMPOSER -->
        <div class="p-3 sm:p-4 border-t border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#1B2230]/80 backdrop-blur-md shrink-0">
            <form id="message-form" onsubmit="handleSendMessage(event)" class="flex items-center gap-2">
                <!-- Ephemeral Duration Selector -->
                <div class="relative shrink-0">
                    <select id="ephemeral_duration" title="Pesan Sementara (Ephemeral)"
                        class="px-2.5 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#222B3A] text-xs text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] transition">
                        <option value="0">⏱️ Normal</option>
                        <option value="10">⏱️ 10 dtk</option>
                        <option value="30">⏱️ 30 dtk</option>
                        <option value="60">⏱️ 1 mnt</option>
                        <option value="300">⏱️ 5 mnt</option>
                    </select>
                </div>

                <!-- Text Input -->
                <input type="text" id="message-input" placeholder="Tulis pesan privat di sini... (Enter untuk kirim)" autocomplete="off"
                    class="flex-1 px-4 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-xs sm:text-sm text-[#182033] dark:text-[#F1F4FA] placeholder-[#667085] dark:placeholder-[#A8B2C3]/60 focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF] transition">

                <!-- Send Button -->
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-[#008069] hover:bg-[#00A884] dark:bg-[#00A884] dark:hover:bg-[#25D366] text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-1.5 shrink-0">
                    <span class="hidden sm:inline">Kirim</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <!-- 4. SLIDING MEMBERS DRAWER -->
        <aside id="members-drawer" class="hidden absolute top-0 right-0 bottom-0 w-72 max-w-[85vw] border-l border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#1B2230] p-4 flex flex-col justify-between z-30 shadow-xl overflow-y-auto">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#E3E7EF] dark:border-[#344052]">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-bold text-[#182033] dark:text-[#F1F4FA] uppercase tracking-wider">Daftar Anggota</h3>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#EEF1F7] dark:bg-[#222B3A] text-[#667085] dark:text-[#A8B2C3] font-mono">
                            {{ count($metadata['participants']) }} / {{ $room->max_participants }}
                        </span>
                    </div>
                    <button type="button" onclick="toggleMembersDrawer()" class="text-[#667085] hover:text-[#182033] dark:hover:text-[#F1F4FA] text-base font-bold">&times;</button>
                </div>

                <div class="space-y-2">
                    @foreach($metadata['participants'] as $p)
                        <div class="p-2.5 rounded-xl bg-[#EEF1F7]/40 dark:bg-[#222B3A]/50 border border-[#E3E7EF] dark:border-[#344052] flex items-center justify-between gap-2 shadow-2xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 uppercase" style="background-color: {{ $p['avatar_color'] }}">
                                    {{ substr($p['display_name'], 0, 1) }}
                                </div>
                                <div class="truncate">
                                    <div class="text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] truncate">{{ $p['display_name'] }}</div>
                                    <div class="flex items-center gap-1">
                                        @if($p['role'] === 'creator')
                                            <span class="text-[9px] font-bold text-[#315CE8] dark:text-[#7895FF]">Creator</span>
                                        @else
                                            <span class="text-[9px] text-[#667085] dark:text-[#A8B2C3]">Anggota</span>
                                        @endif
                                        <span class="text-[8px] text-emerald-500 font-mono">● online</span>
                                    </div>
                                </div>
                            </div>

                            @if($metadata['is_creator'] && $p['user_id'] !== $user->user_id)
                                <button type="button" onclick="kickUser('{{ $p['user_id'] }}', '{{ $p['display_name'] }}')" class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[10px] font-semibold" title="Keluarkan Peserta">
                                    Kick
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Creator Control Panel -->
            @if($metadata['is_creator'])
                <div class="pt-4 border-t border-[#E3E7EF] dark:border-[#344052] space-y-2">
                    <h4 class="text-[10px] font-bold text-[#667085] dark:text-[#A8B2C3] uppercase tracking-wider">Kontrol Creator</h4>
                    <button type="button" onclick="toggleRoomLock()" class="w-full py-2 px-3 rounded-xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] transition flex items-center justify-center gap-1.5">
                        <span id="lock-btn-label">{{ $room->isLocked() ? 'Buka Kunci Room' : 'Kunci Room' }}</span>
                    </button>
                </div>
            @endif
        </aside>
    </main>
</div>

<!-- Invitation & QR Code Modal -->
<div id="invite-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-[#E3E7EF] dark:border-[#344052]">
            <h3 class="text-sm font-bold text-[#182033] dark:text-[#F1F4FA]">Undang Peserta ke Room</h3>
            <button type="button" onclick="closeInviteModal()" class="text-[#667085] hover:text-[#182033] dark:hover:text-[#F1F4FA] text-lg font-bold">&times;</button>
        </div>

        <div class="text-center py-4 bg-[#EEF1F7]/50 dark:bg-[#151A24] rounded-xl border border-dashed border-[#E3E7EF] dark:border-[#344052]">
            <div class="text-xs font-mono font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-widest text-lg">{{ $room->room_id }}</div>
            <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">Bagikan Room ID ini untuk akses langsung</p>
        </div>

        <!-- Dynamic QR Code Canvas -->
        <div class="flex flex-col items-center justify-center p-3 bg-white rounded-xl border border-[#E3E7EF] mx-auto w-44 h-44 shadow-2xs">
            <canvas id="invite-qr-canvas" class="w-36 h-36 rounded-lg"></canvas>
            <span class="text-[9px] font-mono text-slate-500 mt-1">QR Undangan WebRTC</span>
        </div>

        <div>
            <label class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">Tautan Undangan</label>
            <div class="flex items-center gap-2">
                <input type="text" id="invite-link-input" readonly value="{{ route('rooms.join', ['room_id' => $room->room_id]) }}"
                    class="w-full px-3 py-2 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-[#EEF1F7]/60 dark:bg-[#222B3A] text-xs font-mono text-[#667085] dark:text-[#A8B2C3]">
                <button type="button" onclick="copyInviteLink()" class="px-3 py-2 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white text-xs font-semibold transition shrink-0">
                    Salin
                </button>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" onclick="closeInviteModal()" class="w-full py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#667085] dark:text-[#A8B2C3] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A]">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const currentRoomId = "{{ $room->room_id }}";
    const currentUserId = "{{ $user->user_id }}";
    const currentUserName = "{{ $user->display_name }}";
    const currentUserAvatar = "{{ $user->avatar_color }}";

    let activeReply = null; // { messageId, authorName, text }

    function copyRoomId(id) {
        navigator.clipboard.writeText(id).then(() => alert('Room ID ' + id + ' disalin!'));
    }

    function toggleMembersDrawer() {
        const drawer = document.getElementById('members-drawer');
        drawer.classList.toggle('hidden');
    }

    function openInviteModal() {
        document.getElementById('invite-modal').classList.remove('hidden');
        const canvas = document.getElementById('invite-qr-canvas');
        const inviteUrl = document.getElementById('invite-link-input').value;
        if (canvas && window.renderQrToCanvas) {
            window.renderQrToCanvas(canvas, inviteUrl, { size: 144 });
        }
    }

    function closeInviteModal() {
        document.getElementById('invite-modal').classList.add('hidden');
    }

    function copyInviteLink() {
        const input = document.getElementById('invite-link-input');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => alert('Tautan undangan disalin!'));
    }

    function setReply(messageId, authorName, text) {
        activeReply = { messageId, authorName, text };
        document.getElementById('reply-author').textContent = authorName;
        document.getElementById('reply-text').textContent = text;
        document.getElementById('reply-bar').classList.remove('hidden');
        document.getElementById('message-input').focus();
    }

    function cancelReply() {
        activeReply = null;
        const bar = document.getElementById('reply-bar');
        if (bar) bar.classList.add('hidden');
    }
    window.cancelReply = cancelReply;

    function addReaction(bubbleElement, emoji) {
        let reactionContainer = bubbleElement.querySelector('.reaction-pills');
        if (!reactionContainer) {
            reactionContainer = document.createElement('div');
            reactionContainer.className = 'reaction-pills flex items-center gap-1 mt-1';
            bubbleElement.appendChild(reactionContainer);
        }

        let existingPill = Array.from(reactionContainer.children).find(p => p.dataset.emoji === emoji);
        if (existingPill) {
            let countSpan = existingPill.querySelector('.count');
            countSpan.textContent = parseInt(countSpan.textContent) + 1;
        } else {
            const pill = document.createElement('span');
            pill.className = 'px-1.5 py-0.5 rounded-full bg-[#EEF1F7] dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-[10px] flex items-center gap-0.5 cursor-pointer';
            pill.dataset.emoji = emoji;
            pill.innerHTML = `${emoji} <span class="count text-[9px] font-bold">1</span>`;
            reactionContainer.appendChild(pill);
        }
    }

    function handleSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        if (!text) return;

        const duration = parseInt(document.getElementById('ephemeral_duration').value);
        const replyPayload = activeReply;
        cancelReply();

        // Create UI Bubble for Sender
        const list = document.getElementById('messages-list');
        const now = new Date();
        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const bubbleId = 'bubble_' + Math.random().toString(36).substr(2, 9);

        const bubbleWrapper = document.createElement('div');
        bubbleWrapper.id = bubbleId;
        bubbleWrapper.className = 'flex items-start justify-end gap-2.5 group';

        let replyHtml = '';
        if (replyPayload) {
            replyHtml = `
                <div class="mb-1 p-2 rounded-lg bg-[#C2E8BC]/80 dark:bg-[#004A3C]/80 text-[#111B21] dark:text-[#E9EDEF] border-l-2 border-[#00A884] text-[11px]">
                    <div class="font-bold text-[#008069] dark:text-[#25D366]">${replyPayload.authorName}</div>
                    <div class="truncate opacity-85">${replyPayload.text}</div>
                </div>
            `;
        }

        bubbleWrapper.innerHTML = `
            <div class="flex flex-col items-end max-w-[85%] sm:max-w-[70%]">
                <div class="flex items-center gap-1.5 text-[11px] text-[#667781] dark:text-[#8696A0] mb-1">
                    <span>${timeStr}</span>
                    <span class="font-semibold text-[#111B21] dark:text-[#E9EDEF]">Anda</span>
                </div>
                <div class="relative p-3.5 rounded-2xl rounded-tr-sm bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] border border-[#C5E8B7] dark:border-[#004D3F] text-xs leading-relaxed shadow-xs">
                    ${replyHtml}
                    <div>${text}</div>

                    <!-- Quick Reactions & Actions on Hover -->
                    <div class="absolute -top-3 right-2 hidden group-hover:flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] shadow-sm text-xs">
                        <button type="button" onclick="addReaction(this.closest('.flex-col'), '👍')" title="Thumbs up">👍</button>
                        <button type="button" onclick="addReaction(this.closest('.flex-col'), '❤️')" title="Love">❤️</button>
                        <button type="button" onclick="addReaction(this.closest('.flex-col'), '🔒')" title="Secured">🔒</button>
                        <button type="button" onclick="navigator.clipboard.writeText('${text.replace(/'/g, "\\'")}'); alert('Teks disalin!')" title="Salin">📋</button>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-[#667781] dark:text-[#8696A0] mt-1 font-mono">
                    <span class="text-[#53BDEB] font-bold">✓✓ Delivered</span>
                    ${duration > 0 ? `<span class="countdown-badge text-amber-500 font-bold">&bull; Ephemeral (${duration}s)</span>` : ''}
                </div>
            </div>
        `;

        list.appendChild(bubbleWrapper);
        input.value = '';

        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;

        // Ephemeral countdown timer
        if (duration > 0) {
            let remaining = duration;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const timer = setInterval(() => {
                remaining--;
                if (badge) badge.textContent = `• Ephemeral (${remaining}s)`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    bubbleWrapper.style.transition = 'opacity 0.5s, transform 0.5s';
                    bubbleWrapper.style.opacity = '0';
                    bubbleWrapper.style.transform = 'translateY(-10px)';
                    setTimeout(() => bubbleWrapper.remove(), 500);
                }
            }, 1000);
        }

        // Broadcast over WebRTC DataChannel if peerManager is ready
        if (window.peerManager) {
            window.peerManager.broadcastMessage(text, duration, replyPayload);
        }
    }

    function renderIncomingMessage(data) {
        const list = document.getElementById('messages-list');
        const bubbleId = 'bubble_peer_' + data.id;

        const bubbleWrapper = document.createElement('div');
        bubbleWrapper.id = bubbleId;
        bubbleWrapper.className = 'flex items-start gap-2.5 group';

        let replyHtml = '';
        if (data.replyTo) {
            replyHtml = `
                <div class="mb-1 p-2 rounded-lg bg-[#F0F2F5] dark:bg-[#111B21] border-l-2 border-[#00A884] text-[11px]">
                    <div class="font-bold text-[#008069] dark:text-[#25D366]">${data.replyTo.authorName}</div>
                    <div class="truncate opacity-80 text-[#111B21] dark:text-[#E9EDEF]">${data.replyTo.text}</div>
                </div>
            `;
        }

        bubbleWrapper.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-[#008069] text-white flex items-center justify-center text-xs font-bold shrink-0 uppercase shadow-2xs">
                ${(data.senderName || 'P').charAt(0)}
            </div>
            <div class="flex flex-col items-start max-w-[85%] sm:max-w-[70%]">
                <div class="flex items-center gap-1.5 text-[11px] text-[#667781] dark:text-[#8696A0] mb-1">
                    <span class="font-semibold text-[#111B21] dark:text-[#E9EDEF]">${data.senderName}</span>
                    <span>${data.timestamp}</span>
                </div>
                <div class="relative p-3.5 rounded-2xl rounded-tl-sm bg-white dark:bg-[#202C33] border border-[#E9EDEF] dark:border-[#222D34] text-xs text-[#111B21] dark:text-[#E9EDEF] leading-relaxed shadow-xs">
                    ${replyHtml}
                    <div>${data.text}</div>

                    <!-- Quick Reactions & Reply on Hover -->
                    <div class="absolute -top-3 left-2 hidden group-hover:flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] shadow-sm text-xs">
                        <button type="button" onclick="setReply('${data.id}', '${data.senderName.replace(/'/g, "\\'")}', '${data.text.replace(/'/g, "\\'")}')" title="Balas">↩️</button>
                        <button type="button" onclick="addReaction(this.closest('.flex-col'), '👍')" title="Thumbs up">👍</button>
                        <button type="button" onclick="addReaction(this.closest('.flex-col'), '❤️')" title="Love">❤️</button>
                        <button type="button" onclick="navigator.clipboard.writeText('${data.text.replace(/'/g, "\\'")}'); alert('Teks disalin!')" title="Salin">📋</button>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-[#667781] dark:text-[#8696A0] mt-1 font-mono">
                    <span class="text-[#25D366] font-bold">✓ E2EE Enkripsi</span>
                    ${data.ephemeralDuration > 0 ? `<span class="countdown-badge text-amber-500 font-bold">&bull; Ephemeral (${data.ephemeralDuration}s)</span>` : ''}
                </div>
            </div>
        `;

        list.appendChild(bubbleWrapper);
        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;

        if (data.ephemeralDuration > 0) {
            let remaining = data.ephemeralDuration;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const timer = setInterval(() => {
                remaining--;
                if (badge) badge.textContent = `• Ephemeral (${remaining}s)`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    bubbleWrapper.style.transition = 'opacity 0.5s, transform 0.5s';
                    bubbleWrapper.style.opacity = '0';
                    bubbleWrapper.style.transform = 'translateY(-10px)';
                    setTimeout(() => bubbleWrapper.remove(), 500);
                }
            }, 1000);
        }
    }

    // Initialize WebRTC Peer Engine
    document.addEventListener('DOMContentLoaded', () => {
        if (window.SecureLinkPeer) {
            window.peerManager = new window.SecureLinkPeer({
                roomId: currentRoomId,
                userId: currentUserId,
                userName: currentUserName,
                userAvatar: currentUserAvatar,
                onMessageReceived: renderIncomingMessage,
                onStatusChange: (status) => {
                    const statusEl = document.getElementById('header-conn-status');
                    if (statusEl) {
                        statusEl.textContent = `● ${status.label}`;
                        statusEl.className = `font-semibold font-sans ${status.color}`;
                    }
                }
            });

            // Echo Signaling Integration if Reverb/Echo is active
            if (window.Echo) {
                try {
                    const channel = window.Echo.join(`room.${currentRoomId}`);
                    channel.here((users) => {
                        const countEl = document.getElementById('members-count');
                        if (countEl) countEl.textContent = users.length;
                        users.forEach(u => {
                            if (u.id !== currentUserId && currentUserId < u.id) {
                                window.peerManager.initiateOffer(u.id);
                            }
                        });
                    });

                    channel.joining((u) => {
                        const countEl = document.getElementById('members-count');
                        if (countEl) countEl.textContent = parseInt(countEl.textContent || '1') + 1;
                        if (currentUserId < u.id) {
                            window.peerManager.initiateOffer(u.id);
                        }
                    });

                    channel.leaving(() => {
                        const countEl = document.getElementById('members-count');
                        if (countEl) countEl.textContent = Math.max(1, parseInt(countEl.textContent || '1') - 1);
                    });

                    channel.listenForWhisper('signal', (data) => {
                        if (data.to === currentUserId && window.peerManager) {
                            window.peerManager.handleSignal(data.from, data.signal);
                        }
                    });

                    window.peerManager.signalSender = (targetUserId, signal) => {
                        channel.whisper('signal', {
                            from: currentUserId,
                            to: targetUserId,
                            signal: signal
                        });
                    };
                } catch (e) {
                    console.log('[SecureLink] Echo signaling running in local direct mode:', e);
                }
            }
        }
    });

    function toggleRoomLock() {
        fetch(`/api/rooms/${currentRoomId}/lock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status lock');
            }
        });
    }

    function kickUser(targetUserId, targetName) {
        if (!confirm(`Keluarkan ${targetName} dari room?`)) return;

        fetch(`/api/rooms/${currentRoomId}/kick`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ target_user_id: targetUserId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal mengeluarkan peserta');
            }
        });
    }

    function filterSidebarRooms() {
        const query = document.getElementById('sidebar-search').value.toLowerCase().trim();
        const items = document.querySelectorAll('.sidebar-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
