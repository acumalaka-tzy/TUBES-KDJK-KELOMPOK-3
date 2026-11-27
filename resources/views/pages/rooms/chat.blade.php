@extends('layouts.app')

@section('title', $room->room_name . ' — SecureLink')

@section('app_shell')
@php
    $isGroup = $room->max_participants > 2;
    $allRooms = isset($userRooms) ? $userRooms : collect();
@endphp

<div class="h-screen w-screen overflow-hidden flex bg-[var(--sl-page-bg)] text-[var(--sl-text-primary)] relative">

    <!-- A. Slim Icon Navigation Sidebar -->
    @include('partials.app-nav')

    <!-- B. Conversation Sidebar -->
    <aside id="chat-sidebar" class="w-full md:w-72 lg:w-80 border-r border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex-col shrink-0 md:flex hidden absolute md:static inset-0 z-40 h-full">
        <!-- Header: Brand, User, Single New Chat Button -->
        <div class="px-3.5 py-3 border-b border-[var(--sl-divider)] flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-0.5 shrink-0" style="background-color: {{ $user->avatar_color }}">
                    {!! $user->avatar_html !!}
                </div>
                <span class="text-xs font-bold text-[var(--sl-text-primary)] truncate">{{ $user->display_name }}</span>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" onclick="openNewChatModal('menu')" class="px-2.5 py-1 rounded-lg sl-btn-coral text-[11px] font-semibold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New Chat</span>
                </button>
                <button type="button" onclick="toggleChatSidebar()" class="md:hidden p-1.5 rounded-lg text-[var(--sl-text-secondary)] font-bold text-sm" title="Tutup">&times;</button>
            </div>
        </div>

        <!-- Search & Filter Tabs -->
        <div class="px-3 py-2 border-b border-[var(--sl-divider)] space-y-2 shrink-0">
            <input type="text" id="sidebar-search" placeholder="Cari percakapan atau Room ID..." oninput="filterSidebarRooms()"
                class="w-full px-3 py-1.5 rounded-lg sl-input text-xs">

            <div class="flex items-center gap-1 text-[11px] font-semibold">
                <button type="button" onclick="setSidebarFilter('all')" data-sfilter="all" class="sfilter-pill px-2 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition">All</button>
                <button type="button" onclick="setSidebarFilter('private')" data-sfilter="private" class="sfilter-pill px-2 py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Private</button>
                <button type="button" onclick="setSidebarFilter('group')" data-sfilter="group" class="sfilter-pill px-2 py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Groups</button>
                <button type="button" onclick="setSidebarFilter('unread')" data-sfilter="unread" class="sfilter-pill px-2 py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Unread</button>
            </div>
        </div>

        <!-- Conversation List -->
        <div id="sidebar-rooms-list" class="flex-1 overflow-y-auto sl-scroll divide-y divide-[var(--sl-divider)]/50">
            @forelse($allRooms as $uPart)
                @php
                    $uRoom = $uPart->room;
                    $isActive = ($uRoom->room_id === $room->room_id);
                    $uIsGroup = $uRoom->max_participants > 2;
                @endphp
                <a href="{{ route('rooms.show', $uRoom->room_id) }}"
                   class="sidebar-item flex items-center justify-between gap-2.5 px-3.5 py-2.5 transition {{ $isActive ? 'bg-[var(--sl-active-conv)]' : 'hover:bg-[var(--sl-hover)]' }}"
                   data-name="{{ strtolower($uRoom->room_name) }}"
                   data-id="{{ strtolower($uRoom->room_id) }}"
                   data-type="{{ $uIsGroup ? 'group' : 'private' }}"
                   data-unread="0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-full {{ $uIsGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center uppercase">
                                {{ strtoupper(substr($uRoom->room_name, 0, $uIsGroup ? 2 : 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-[var(--sl-success)] ring-2 ring-[var(--sl-conv-bg)]"></span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold truncate {{ $isActive ? 'text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-primary)]' }}">{{ $uRoom->room_name }}</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">
                                {{ $uIsGroup ? ($uRoom->active_participants_count ?? 1) . ' anggota' : 'Private P2P' }} &middot; {{ $uRoom->room_id }}
                            </div>
                        </div>
                    </div>
                    <span class="text-[10px] text-[var(--sl-text-secondary)] shrink-0">{{ $uPart->last_seen_at?->format('H:i') }}</span>
                </a>
            @empty
                <div class="p-4 text-center text-xs text-[var(--sl-text-secondary)]">
                    Room aktif: <strong class="text-[var(--sl-text-primary)]">{{ $room->room_name }}</strong>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- C. Main Conversation Area -->
    <main class="flex-1 flex flex-col min-w-0 bg-[var(--sl-chat-bg)] relative h-full">

        <!-- Conversation Header -->
        <header class="px-3 sm:px-5 py-2.5 border-b border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <button type="button" onclick="toggleChatSidebar()" class="md:hidden p-1.5 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] shrink-0" title="Daftar Percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </button>
                <a href="{{ route('dashboard') }}" class="md:hidden p-1.5 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] shrink-0" title="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <div class="w-9 h-9 rounded-full {{ $isGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center shrink-0 uppercase">
                    {{ strtoupper(substr($room->room_name, 0, $isGroup ? 2 : 1)) }}
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm font-bold text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</h1>
                        <span class="hidden sm:inline text-[10px] px-1.5 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] font-semibold">
                            {{ $isGroup ? 'Group' : 'Private' }}
                        </span>
                        @if($room->isLocked())
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-[var(--sl-accent-coral)]/15 text-[var(--sl-accent-coral)] font-semibold">Terkunci</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--sl-text-secondary)] mt-0.5">
                        <span class="font-mono truncate">{{ $room->room_id }}</span>
                        <span>&middot;</span>
                        <span id="header-conn-status" class="font-semibold text-[var(--sl-primary-teal)] truncate">Menunggu Peer...</span>
                    </div>
                </div>
            </div>

            <!-- Header Controls -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- P2P vs Broadcast Mode Toggle -->
                <div class="hidden sm:flex items-center p-0.5 rounded-lg bg-[var(--sl-chat-bg)] border border-[var(--sl-divider)] text-[11px] font-semibold">
                    <button type="button" id="mode-btn-p2p" onclick="setCommunicationMode('p2p')" class="px-2.5 py-1 rounded-md bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] transition">
                        P2P WebRTC
                    </button>
                    <button type="button" id="mode-btn-broadcast" onclick="setCommunicationMode('broadcast')" class="px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition">
                        Broadcast
                    </button>
                </div>

                @if($metadata['is_creator'] && !empty($metadata['current_dynamic_pin']))
                    <div class="hidden xl:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[11px]">
                        <span class="text-[var(--sl-text-secondary)]">TOTP:</span>
                        <span class="font-mono font-bold text-[var(--sl-primary-teal)]">{{ $metadata['current_dynamic_pin'] }}</span>
                    </div>
                @endif

                <button type="button" onclick="openInviteModal()" class="px-2.5 py-1.5 rounded-lg sl-btn-secondary text-xs font-semibold flex items-center gap-1.5" title="QR Code &amp; Undangan">
                    <svg class="w-4 h-4 text-[var(--sl-primary-teal)]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/><path d="M17 17h4v4h-4z"/></svg>
                    <span class="hidden sm:inline">Undang</span>
                </button>

                <button type="button" onclick="toggleMembersDrawer()" class="px-2.5 py-1.5 rounded-lg bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] text-xs font-semibold flex items-center gap-1.5" title="Informasi &amp; Anggota">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span id="members-count">{{ count($metadata['participants']) }}</span>
                </button>

                <form action="{{ route('rooms.leave.web', $room->room_id) }}" method="POST" onsubmit="event.preventDefault(); window.slConfirm({ title: 'Keluar Room', message: 'Apakah Anda yakin ingin keluar dari room ini?', confirmLabel: 'Keluar Room', cancelLabel: 'Batal', onConfirm: () => this.submit() });">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-[var(--sl-accent-coral)] hover:bg-[var(--sl-accent-coral)]/15 transition" title="Keluar Room">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 overflow-y-auto sl-scroll p-4 sm:p-5 space-y-3">
            <div class="flex flex-col items-center gap-1.5 my-1">
                <span class="px-2.5 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[10px] font-semibold text-[var(--sl-text-secondary)]">Hari Ini</span>
                <div id="mode-security-banner" class="px-3 py-1 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[11px] text-[var(--sl-text-secondary)] text-center">
                    <span id="mode-security-text">P2P WebRTC DataChannel aktif &middot; E2EE AES-GCM &middot; Server tidak menyimpan pesan</span>
                </div>
            </div>

            <div id="messages-list" class="space-y-3">
                <div class="flex justify-center">
                    <span class="sl-chat-msg text-[var(--sl-text-secondary)] bg-[var(--sl-elevated)] px-3 py-1 rounded-lg border border-[var(--sl-divider)]">
                        Anda bergabung ke <strong class="text-[var(--sl-text-primary)]">{{ $room->room_name }}</strong> ({{ $isGroup ? 'Group P2P Mesh' : 'Private 1-on-1' }})
                    </span>
                </div>
            </div>
        </div>

        <!-- Reply Preview Bar -->
        <div id="reply-bar" class="hidden px-4 py-2 bg-[var(--sl-active-conv)] border-t border-[var(--sl-divider)] flex items-center justify-between text-xs shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-[var(--sl-primary-teal)] font-bold">Membalas:</span>
                <span id="reply-author" class="font-semibold text-[var(--sl-text-primary)]"></span>
                <span id="reply-text" class="text-[var(--sl-text-secondary)] truncate max-w-xs"></span>
            </div>
            <button type="button" onclick="cancelReply()" class="text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] font-bold text-sm">&times;</button>
        </div>

        <!-- Quick Emoji Picker Popover -->
        <div id="emoji-popover" class="hidden absolute bottom-16 left-4 z-30 p-2 rounded-lg sl-surface shadow-md flex items-center gap-1.5 text-base">
            @foreach(['👍', '❤️', '🔒', '✅', '😂', '🎉', '🔥', '👋'] as $em)
                <button type="button" onclick="insertEmoji('{{ $em }}')" class="p-1 rounded hover:bg-[var(--sl-hover)]">{{ $em }}</button>
            @endforeach
        </div>

        <!-- Message Composer -->
        <div class="p-3 border-t border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] shrink-0">
            <form id="message-form" onsubmit="handleSendMessage(event)" class="flex items-center gap-2">
                <button type="button" onclick="toggleEmojiPicker()" class="p-2 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] shrink-0" title="Emoji">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                </button>

                <select id="ephemeral_duration" aria-label="Durasi Pesan Ephemeral" title="Pesan Sementara (Ephemeral)"
                    class="px-2 py-2 rounded-lg sl-input text-xs shrink-0">
                    <option value="0">Normal</option>
                    <option value="10">10 dtk</option>
                    <option value="30">30 dtk</option>
                    <option value="60">1 mnt</option>
                    <option value="300">5 mnt</option>
                </select>

                <input type="text" id="message-input" aria-label="Tulis pesan" placeholder="Tulis pesan..." autocomplete="off"
                    class="flex-1 px-3.5 py-2 rounded-lg sl-input text-xs sm:text-sm">

                <button type="submit" class="px-4 py-2 rounded-lg sl-btn-coral font-semibold text-xs sm:text-sm flex items-center gap-1.5 shrink-0">
                    <span class="hidden sm:inline">Kirim</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        </div>
    </main>

    <!-- D. Collapsible Right Information Panel -->
    <aside id="members-drawer" class="hidden absolute md:static top-0 right-0 bottom-0 w-80 max-w-[90vw] border-l border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex flex-col z-30 overflow-y-auto sl-scroll shrink-0 h-full">
        <div class="p-4 border-b border-[var(--sl-divider)] flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--sl-text-primary)]">
                {{ $isGroup ? 'Info Grup & Keamanan' : 'Info Percakapan' }}
            </h2>
            <button type="button" onclick="toggleMembersDrawer()" class="text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] text-base font-bold">&times;</button>
        </div>

        <div class="p-4 space-y-5 text-xs">
            <div class="space-y-1">
                <div class="font-bold text-sm text-[var(--sl-text-primary)]">{{ $room->room_name }}</div>
                <div class="font-mono text-[11px] text-[var(--sl-text-secondary)]">Room ID: {{ $room->room_id }}</div>
            </div>

            <!-- Security & Connection Diagnostics -->
            <div class="p-3 rounded-lg bg-[var(--sl-elevated)] border border-[var(--sl-divider)] space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Keamanan &amp; Koneksi</div>
                <div class="flex justify-between">
                    <span class="text-[var(--sl-text-secondary)]">Koneksi</span>
                    <span id="diag-conn-type" class="font-semibold text-[var(--sl-text-primary)]">Menunggu Peer</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--sl-text-secondary)]">WebRTC / ICE</span>
                    <span id="diag-ice-state" class="font-mono text-[var(--sl-text-primary)]">new</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--sl-text-secondary)]">E2EE (AES-GCM)</span>
                    <span id="diag-e2ee-state" class="font-semibold text-[var(--sl-text-secondary)]">Menunggu Handshake</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--sl-text-secondary)]">Verifikasi Peer</span>
                    <span class="font-semibold text-[var(--sl-text-secondary)]">Not Verified</span>
                </div>
            </div>

            <!-- Members List -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Anggota</span>
                    <span class="text-[11px] font-mono text-[var(--sl-text-secondary)]">{{ count($metadata['participants']) }} / {{ $room->max_participants }}</span>
                </div>
                <div class="divide-y divide-[var(--sl-divider)]/60 border border-[var(--sl-divider)] rounded-lg overflow-hidden">
                    @foreach($metadata['participants'] as $p)
                        <div class="p-2.5 flex items-center justify-between gap-2 bg-[var(--sl-elevated)]">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 uppercase overflow-hidden p-0.5" style="background-color: {{ $p['avatar_color'] }}">
                                    {!! $p['avatar_html'] ?? ($p['avatar_display'] ?? strtoupper(substr($p['display_name'], 0, 1))) !!}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">{{ $p['display_name'] }}</div>
                                    <div class="text-[10px] text-[var(--sl-text-secondary)]">{{ $p['role'] === 'creator' ? 'Admin' : 'Anggota' }}</div>
                                </div>
                            </div>
                            @if($metadata['is_creator'] && $p['user_id'] !== $user->user_id)
                                <button type="button" onclick="kickUser('{{ $p['user_id'] }}', '{{ $p['display_name'] }}')" class="text-[11px] font-semibold text-[var(--sl-accent-coral)] hover:underline">Kick</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Invitation Management -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Undangan Aktif</span>
                    <button type="button" onclick="openInviteModal()" class="text-[11px] font-semibold text-[var(--sl-primary-teal)] hover:underline">+ Buat QR</button>
                </div>
                <div id="info-invites-list" class="space-y-1.5 text-[11px] text-[var(--sl-text-secondary)]">
                    <div>Memuat daftar undangan...</div>
                </div>
            </div>

            @if($metadata['is_creator'])
                <div class="pt-3 border-t border-[var(--sl-divider)] space-y-2">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Pengaturan Admin</div>
                    <button type="button" onclick="toggleRoomLock()" class="w-full py-2 px-3 rounded-lg sl-btn-secondary text-xs font-semibold">
                        <span id="lock-btn-label">{{ $room->isLocked() ? 'Buka Kunci Room' : 'Kunci Room' }}</span>
                    </button>
                </div>
            @endif
        </div>
    </aside>
</div>

<!-- E. QR Code & Invitation Modal -->
<div id="invite-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
    <div class="sl-surface rounded-xl max-w-sm w-full p-5 space-y-4 shadow-lg max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2.5 border-b border-[var(--sl-divider)]">
            <div>
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">{{ $room->room_name }}</h3>
                <p class="text-[11px] text-[var(--sl-text-secondary)]">Bagikan QR Code atau tautan undangan bertoken</p>
            </div>
            <button type="button" onclick="closeInviteModal()" class="text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] text-lg font-bold">&times;</button>
        </div>

        <div class="flex flex-col items-center justify-center p-4 bg-white rounded-lg border border-[var(--sl-divider)]">
            <canvas id="invite-qr-canvas" class="w-44 h-44"></canvas>
            <div id="invite-status-text" class="text-[11px] text-[#7D8B8D] mt-2 text-center">Membuat token undangan...</div>
        </div>

        <div class="space-y-2">
            <label class="block text-xs font-semibold text-[var(--sl-text-primary)]">Tautan Undangan</label>
            <div class="flex items-center gap-2">
                <input type="text" id="invite-link-input" readonly value="{{ route('rooms.join', ['room_id' => $room->room_id]) }}"
                    class="w-full px-3 py-2 rounded-lg sl-input text-xs font-mono">
                <button type="button" onclick="copyInviteLink()" class="px-3 py-2 rounded-lg sl-btn-primary text-xs font-semibold shrink-0">Salin</button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="downloadCurrentQr()" class="py-2 px-3 rounded-lg sl-btn-secondary text-xs font-semibold">
                Download QR
            </button>
            @if($metadata['is_creator'])
                <button type="button" id="modal-revoke-btn" onclick="revokeCurrentInvite()" class="py-2 px-3 rounded-lg border border-[var(--sl-accent-coral)]/40 text-[var(--sl-accent-coral)] hover:bg-[var(--sl-accent-coral)]/10 text-xs font-semibold disabled:opacity-40" disabled>
                    Cabut Token
                </button>
            @else
                <button type="button" onclick="closeInviteModal()" class="py-2 px-3 rounded-lg sl-btn-secondary text-xs font-semibold">Tutup</button>
            @endif
        </div>
    </div>
</div>

@include('partials.new-chat-modal')

<script>
    const currentRoomId = "{{ $room->room_id }}";
    const currentUserId = "{{ $user->user_id }}";
    const currentUserName = "{{ $user->display_name }}";
    const currentUserAvatar = "{{ $user->avatar_color }}";
    const currentUserAvatarIcon = "{{ $user->avatar_icon }}";
    const isCreator = {{ $metadata['is_creator'] ? 'true' : 'false' }};
    const isGroupRoom = {{ $isGroup ? 'true' : 'false' }};
    const avatarSvgs = @json(\App\Models\UserSession::$avatarSvgs);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let activeInviteId = null;
    let activeReply = null;
    let communicationMode = 'p2p';
    let currentSidebarFilter = 'all';

    function getAvatarMarkup(icon, display, name) {
        if (icon && avatarSvgs[icon]) {
            return `<div class="w-full h-full flex items-center justify-center p-0.5 pointer-events-none">${avatarSvgs[icon]}</div>`;
        }
        if (display && display.includes('<svg')) {
            return `<div class="w-full h-full flex items-center justify-center p-0.5 pointer-events-none">${display}</div>`;
        }
        return `<span class="font-bold select-none leading-none">${(name || 'P').charAt(0).toUpperCase()}</span>`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function toggleChatSidebar() {
        document.getElementById('chat-sidebar')?.classList.toggle('hidden');
    }
    window.toggleChatSidebar = toggleChatSidebar;

    function toggleMembersDrawer() {
        const drawer = document.getElementById('members-drawer');
        if (!drawer) return;
        drawer.classList.toggle('hidden');
        if (!drawer.classList.contains('hidden')) loadRoomInvites();
    }
    window.toggleMembersDrawer = toggleMembersDrawer;

    function toggleEmojiPicker() {
        document.getElementById('emoji-popover')?.classList.toggle('hidden');
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('message-input');
        if (input) {
            input.value += emoji;
            input.focus();
        }
        document.getElementById('emoji-popover')?.classList.add('hidden');
    }

    function setSidebarFilter(filter) {
        currentSidebarFilter = filter;
        document.querySelectorAll('.sfilter-pill').forEach(btn => {
            const active = btn.getAttribute('data-sfilter') === filter;
            btn.className = active
                ? 'sfilter-pill px-2 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition'
                : 'sfilter-pill px-2 py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition';
        });
        filterSidebarRooms();
    }

    function filterSidebarRooms() {
        const query = (document.getElementById('sidebar-search')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.sidebar-item').forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const id = item.getAttribute('data-id') || '';
            const type = item.getAttribute('data-type') || 'private';
            const unread = item.getAttribute('data-unread') === '1';

            const matchesQuery = name.includes(query) || id.includes(query);
            let matchesFilter = true;
            if (currentSidebarFilter === 'private') matchesFilter = (type === 'private');
            else if (currentSidebarFilter === 'group') matchesFilter = (type === 'group');
            else if (currentSidebarFilter === 'unread') matchesFilter = unread;

            item.style.display = (matchesQuery && matchesFilter) ? 'flex' : 'none';
        });
    }

    async function openInviteModal() {
        document.getElementById('invite-modal').classList.remove('hidden');
        const canvas = document.getElementById('invite-qr-canvas');
        const statusEl = document.getElementById('invite-status-text');
        const linkInput = document.getElementById('invite-link-input');
        const revokeBtn = document.getElementById('modal-revoke-btn');
        statusEl.textContent = 'Membuat token undangan...';

        try {
            const res = await fetch(`/api/rooms/${currentRoomId}/invite`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ expires_in_minutes: 1440 }),
            });
            const data = await res.json();
            if (data.success && data.invite_url) {
                activeInviteId = data.invite_id;
                linkInput.value = data.invite_url;
                if (revokeBtn) revokeBtn.disabled = false;
                statusEl.textContent = 'Berlaku 24 jam • Token terenkripsi';
                await window.renderQrToCanvas?.(canvas, data.invite_url, { size: 176 });
                loadRoomInvites();
                return;
            }
        } catch (err) {}

        statusEl.textContent = 'Tautan langsung Room ID';
        await window.renderQrToCanvas?.(canvas, linkInput.value, { size: 176 });
    }

    function closeInviteModal() {
        document.getElementById('invite-modal').classList.add('hidden');
    }

    function copyInviteLink() {
        const input = document.getElementById('invite-link-input');
        navigator.clipboard.writeText(input.value).then(() => {
            document.getElementById('invite-status-text').textContent = 'Tautan undangan disalin ke clipboard.';
        });
    }

    function downloadCurrentQr() {
        window.downloadQrPng?.(document.getElementById('invite-qr-canvas'), `securelink-${currentRoomId}.png`);
    }

    async function revokeCurrentInvite() {
        if (!activeInviteId) return;
        await revokeInviteById(activeInviteId);
        activeInviteId = null;
        const revokeBtn = document.getElementById('modal-revoke-btn');
        if (revokeBtn) revokeBtn.disabled = true;
        document.getElementById('invite-status-text').textContent = 'Token telah dicabut dan tidak berlaku.';
    }

    async function revokeInviteById(inviteId) {
        try {
            const res = await fetch(`/api/rooms/${currentRoomId}/invites/${inviteId}/revoke`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) loadRoomInvites();
        } catch (err) {}
    }

    async function loadRoomInvites() {
        const container = document.getElementById('info-invites-list');
        if (!container) return;
        try {
            const res = await fetch(`/api/rooms/${currentRoomId}/invites`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!data.success || !data.invites.length) {
                container.innerHTML = '<div>Belum ada undangan dibuat.</div>';
                return;
            }
            container.innerHTML = data.invites.slice(0, 4).map(inv => {
                const badge = inv.status === 'active'
                    ? '<span class="text-[var(--sl-success)] font-semibold">Aktif</span>'
                    : `<span class="text-[var(--sl-accent-coral)] font-semibold">${inv.status}</span>`;
                const revokeAction = (isCreator && inv.status === 'active')
                    ? `<button type="button" onclick="revokeInviteById('${inv.invite_id}')" class="text-[var(--sl-accent-coral)] hover:underline font-semibold">Cabut</button>`
                    : '';
                return `<div class="p-2 rounded bg-[var(--sl-elevated)] border border-[var(--sl-divider)] flex items-center justify-between gap-2">
                    <div>
                        <div class="font-mono text-[10px] text-[var(--sl-text-primary)]">${inv.invite_id}</div>
                        <div>${badge} &middot; Dipakai ${inv.usage_count}${inv.usage_limit ? '/' + inv.usage_limit : ''}</div>
                    </div>
                    ${revokeAction}
                </div>`;
            }).join('');
        } catch (e) {
            container.innerHTML = '<div>Gagal memuat undangan.</div>';
        }
    }

    function copyMessageText(btn) {
        const bubble = btn.closest('.relative');
        const textEl = bubble ? bubble.querySelector('.msg-content') : null;
        if (textEl) {
            navigator.clipboard.writeText(textEl.textContent).then(() => {
                const orig = btn.textContent;
                btn.textContent = 'Disalin!';
                setTimeout(() => { btn.textContent = orig; }, 1200);
            });
        }
    }
    window.copyMessageText = copyMessageText;

    function buildBubbleToolbar(msgId, isOwn) {
        const emojis = ['👍', '❤️', '😂', '🔥', '🔒']
            .map(em => `<button type="button" onclick="reactToMessage('${escapeHtml(msgId)}', '${em}')" class="hover:scale-110 transition px-0.5">${em}</button>`)
            .join('');
        const posClass = isOwn ? 'right-2' : 'left-2';
        return `<div class="msg-toolbar absolute -top-3.5 ${posClass} hidden group-hover:flex items-center gap-1 px-2 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[11px] shadow-xs z-10">
            ${emojis}
            <span class="text-[var(--sl-divider)]">|</span>
            <button type="button" onclick="handleReplyFromBubble(this, '${escapeHtml(msgId)}')" class="hover:text-[var(--sl-primary-teal)] font-medium" title="Balas">Balas</button>
            <button type="button" onclick="copyMessageText(this)" class="hover:text-[var(--sl-primary-teal)] font-medium" title="Salin">Salin</button>
            <button type="button" onclick="deleteMessage('${escapeHtml(msgId)}', ${isOwn ? 'true' : 'false'})" class="text-[var(--sl-accent-coral)] hover:underline font-medium" title="Hapus">Hapus</button>
        </div>`;
    }

    function reactToMessage(msgId, emoji) {
        applyMessageReaction(msgId, emoji);
        if (window.peerManager) {
            window.peerManager.sendReaction(msgId, emoji);
        }
    }
    window.reactToMessage = reactToMessage;

    function applyMessageReaction(msgId, emoji) {
        const wrapper = document.querySelector(`[data-message-id="${msgId}"]`);
        if (!wrapper) return;
        const container = wrapper.querySelector('.reaction-pills');
        if (!container) return;

        container.classList.remove('hidden');
        const existing = container.querySelector(`[data-emoji="${emoji}"]`);
        if (existing) {
            const countEl = existing.querySelector('.rx-count');
            countEl.textContent = parseInt(countEl.textContent || '1') + 1;
        } else {
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.dataset.emoji = emoji;
            pill.onclick = () => reactToMessage(msgId, emoji);
            pill.className = 'px-1.5 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[10px] flex items-center gap-1 text-[var(--sl-text-primary)]';
            pill.innerHTML = `<span>${emoji}</span><span class="rx-count font-bold">1</span>`;
            container.appendChild(pill);
        }
    }
    window.applyMessageReaction = applyMessageReaction;

    function deleteMessage(msgId, isOwn) {
        window.slConfirm({
            title: 'Hapus Pesan',
            message: isOwn ? 'Hapus pesan ini untuk semua peserta?' : 'Hapus pesan ini dari tampilan Anda?',
            confirmLabel: 'Hapus',
            cancelLabel: 'Batal',
            onConfirm: () => {
                applyMessageDeleted(msgId);
                if (isOwn && window.peerManager) {
                    window.peerManager.sendDelete(msgId);
                }
                window.slToast?.('Pesan dihapus.', 'info', 3000);
            }
        });
    }
    window.deleteMessage = deleteMessage;

    function applyMessageDeleted(msgId) {
        const wrapper = document.querySelector(`[data-message-id="${msgId}"]`);
        if (!wrapper) return;
        const contentEl = wrapper.querySelector('.msg-content');
        if (contentEl) {
            contentEl.textContent = 'Pesan ini telah dihapus.';
            contentEl.className = 'msg-content italic text-[var(--sl-text-secondary)]';
        }
        wrapper.querySelector('.msg-toolbar')?.remove();
        wrapper.querySelector('.reaction-pills')?.remove();
    }
    window.applyMessageDeleted = applyMessageDeleted;

    function handleReplyFromBubble(btn, msgId) {
        const wrapper = btn.closest('.group');
        const authorEl = wrapper ? wrapper.querySelector('.sender-name') : null;
        const textEl = wrapper ? wrapper.querySelector('.msg-content') : null;
        setReply(msgId, authorEl ? authorEl.textContent.trim() : 'Peserta', textEl ? textEl.textContent.trim() : '');
    }
    window.handleReplyFromBubble = handleReplyFromBubble;

    function setReply(messageId, authorName, text) {
        activeReply = { messageId, authorName, text };
        document.getElementById('reply-author').textContent = authorName;
        document.getElementById('reply-text').textContent = text;
        document.getElementById('reply-bar').classList.remove('hidden');
        document.getElementById('message-input').focus();
    }

    function cancelReply() {
        activeReply = null;
        document.getElementById('reply-bar')?.classList.add('hidden');
    }
    window.cancelReply = cancelReply;

    function setCommunicationMode(mode) {
        communicationMode = mode === 'broadcast' ? 'broadcast' : 'p2p';
        const p2pBtn = document.getElementById('mode-btn-p2p');
        const bcastBtn = document.getElementById('mode-btn-broadcast');
        const bannerText = document.getElementById('mode-security-text');

        if (communicationMode === 'broadcast') {
            if (p2pBtn) p2pBtn.className = 'px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
            if (bcastBtn) bcastBtn.className = 'px-2.5 py-1 rounded-md bg-[var(--sl-accent-coral)] text-white transition';
            if (bannerText) bannerText.textContent = 'Broadcast Comparison Mode aktif • Pesan diteruskan melalui Server Reverb (Non-P2P)';
        } else {
            if (p2pBtn) p2pBtn.className = 'px-2.5 py-1 rounded-md bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] transition';
            if (bcastBtn) bcastBtn.className = 'px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
            if (bannerText) bannerText.textContent = 'P2P WebRTC DataChannel aktif • E2EE AES-GCM • Server tidak menyimpan pesan';
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

        const list = document.getElementById('messages-list');
        const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const clientMsgId = 'msg_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);

        const bubbleWrapper = document.createElement('div');
        bubbleWrapper.id = 'bubble_' + clientMsgId;
        bubbleWrapper.dataset.messageId = clientMsgId;
        bubbleWrapper.className = 'flex justify-end group';

        let replyHtml = '';
        if (replyPayload) {
            replyHtml = `<div class="mb-1.5 px-2.5 py-1.5 rounded bg-[var(--sl-elevated)]/80 border-l-2 border-[var(--sl-primary-teal)] sl-chat-reply">
                <div class="font-bold text-[var(--sl-primary-teal)]">${escapeHtml(replyPayload.authorName)}</div>
                <div class="truncate text-[var(--sl-text-secondary)]">${escapeHtml(replyPayload.text)}</div>
            </div>`;
        }

        bubbleWrapper.innerHTML = `
            <div class="flex flex-col items-end max-w-[82%] sm:max-w-[68%]">
                <span class="sender-name hidden">Anda</span>
                <div class="relative px-3.5 py-2.5 rounded-xl rounded-tr-sm sl-chat-msg leading-relaxed" style="background-color: var(--sl-bubble-out); color: var(--sl-bubble-out-text);">
                    ${replyHtml}
                    <div class="msg-content break-words whitespace-pre-wrap select-text">${escapeHtml(text)}</div>
                    ${buildBubbleToolbar(clientMsgId, true)}
                </div>
                <div class="reaction-pills hidden flex flex-wrap gap-1 mt-1"></div>
                <div class="flex items-center gap-1.5 text-[10px] text-[var(--sl-text-secondary)] mt-1">
                    <span>${timeStr}</span>
                    <span class="delivery-badge ${communicationMode === 'broadcast' ? 'text-[var(--sl-accent-coral)]' : 'text-[var(--sl-primary-teal)]'} font-semibold">
                        ${communicationMode === 'broadcast' ? '⚠ Server Broadcast' : '✓ Terkirim (P2P)'}
                    </span>
                    ${duration > 0 ? `<span class="countdown-badge text-[var(--sl-accent-coral)] font-semibold">&middot; ⏱ ${duration}s</span>` : ''}
                </div>
            </div>
        `;

        list.appendChild(bubbleWrapper);
        input.value = '';
        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;

        if (duration > 0) {
            let remaining = duration;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const timer = setInterval(() => {
                remaining--;
                if (badge) badge.textContent = `· ⏱ ${remaining}s`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    bubbleWrapper.style.transition = 'opacity 0.35s ease';
                    bubbleWrapper.style.opacity = '0';
                    setTimeout(() => bubbleWrapper.remove(), 350);
                }
            }, 1000);
        }

        if (communicationMode === 'broadcast') {
            fetch(`/api/rooms/${currentRoomId}/broadcast-message`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ message: text, ephemeral_duration: duration, reply_to: replyPayload }),
            }).catch((err) => console.error('[SecureLink] Broadcast error:', err));
        } else if (window.peerManager) {
            window.peerManager.broadcastMessage(text, duration, replyPayload).then((sent) => {
                if (sent && sent.id) {
                    bubbleWrapper.dataset.messageId = sent.id;
                    const toolbar = bubbleWrapper.querySelector('.msg-toolbar');
                    if (toolbar) toolbar.outerHTML = buildBubbleToolbar(sent.id, true);
                }
            });
        }
    }

    function renderIncomingMessage(data) {
        const list = document.getElementById('messages-list');
        const bubbleWrapper = document.createElement('div');
        bubbleWrapper.id = 'bubble_peer_' + data.id;
        bubbleWrapper.dataset.messageId = data.id;
        bubbleWrapper.className = 'flex items-start gap-2 group';

        let replyHtml = '';
        if (data.replyTo) {
            replyHtml = `<div class="mb-1.5 px-2.5 py-1.5 rounded bg-[var(--sl-chat-bg)] border-l-2 border-[var(--sl-primary-teal)] sl-chat-reply">
                <div class="font-bold text-[var(--sl-primary-teal)]">${escapeHtml(data.replyTo.authorName || 'Peserta')}</div>
                <div class="truncate text-[var(--sl-text-secondary)]">${escapeHtml(data.replyTo.text || '')}</div>
            </div>`;
        }

        const senderLabel = isGroupRoom
            ? `<div class="sender-name text-[11px] font-bold text-[var(--sl-primary-teal)] mb-0.5">${escapeHtml(data.senderName)}</div>`
            : `<span class="sender-name hidden">${escapeHtml(data.senderName)}</span>`;

        bubbleWrapper.innerHTML = `
            <div class="w-7 h-7 rounded-full text-white flex items-center justify-center text-xs font-bold shrink-0 uppercase overflow-hidden" style="background-color: ${data.senderAvatarColor || '#497173'}">
                ${getAvatarMarkup(data.senderAvatarIcon, data.senderAvatarDisplay, data.senderName)}
            </div>
            <div class="flex flex-col items-start max-w-[82%] sm:max-w-[68%]">
                <div class="relative px-3.5 py-2.5 rounded-xl rounded-tl-sm bg-[var(--sl-bubble-in)] border border-[var(--sl-divider)] sl-chat-msg text-[var(--sl-text-primary)] leading-relaxed">
                    ${senderLabel}
                    ${replyHtml}
                    <div class="msg-content break-words whitespace-pre-wrap select-text">${escapeHtml(data.text)}</div>
                    ${buildBubbleToolbar(data.id, false)}
                </div>
                <div class="reaction-pills hidden flex flex-wrap gap-1 mt-1"></div>
                <div class="flex items-center gap-1.5 text-[10px] text-[var(--sl-text-secondary)] mt-1">
                    <span>${escapeHtml(data.timestamp || '')}</span>
                    ${data.transport === 'broadcast'
                        ? '<span class="text-[var(--sl-accent-coral)] font-semibold">⚠ Server Broadcast</span>'
                        : '<span class="text-[var(--sl-primary-teal)] font-semibold">✓ E2EE (P2P)</span>'}
                    ${data.ephemeralDuration > 0 ? `<span class="countdown-badge text-[var(--sl-accent-coral)] font-semibold">&middot; ⏱ ${data.ephemeralDuration}s</span>` : ''}
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
                if (badge) badge.textContent = `· ${remaining}s`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    bubbleWrapper.remove();
                }
            }, 1000);
        }
    }

    function updateDiagnosticsUi(diag) {
        const connEl = document.getElementById('diag-conn-type');
        const iceEl = document.getElementById('diag-ice-state');
        const e2eeEl = document.getElementById('diag-e2ee-state');
        if (!connEl || !diag) return;
        connEl.textContent = diag.overall?.label || 'Menunggu Peer';
        if (diag.peers && diag.peers.length > 0) {
            const first = diag.peers[0];
            iceEl.textContent = first.iceState || 'new';
            e2eeEl.textContent = first.e2eeReady ? 'Active (AES-GCM)' : 'Menunggu Handshake';
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Apply saved default ephemeral duration from Settings
        const savedEph = localStorage.getItem('securelink_ephemeral_default');
        const ephEl = document.getElementById('ephemeral_duration');
        if (savedEph && ephEl) ephEl.value = savedEph;

        if (!window.SecureLinkPeer) return;

        window.peerManager = new window.SecureLinkPeer({
            roomId: currentRoomId,
            userId: currentUserId,
            userName: currentUserName,
            userAvatar: currentUserAvatar,
            userAvatarColor: currentUserAvatar,
            userAvatarIcon: currentUserAvatarIcon,
            onMessageReceived: renderIncomingMessage,
            onReactionReceived: (rx) => applyMessageReaction(rx.messageId, rx.emoji),
            onMessageDeleted: (del) => applyMessageDeleted(del.messageId),
            onDiagnosticsUpdate: updateDiagnosticsUi,
            onMessageAck: (messageId) => {
                const badge = document.querySelector(`[data-message-id="${messageId}"] .delivery-badge`);
                if (badge) badge.textContent = '✓✓ Delivered (P2P ACK)';
            },
            onStatusChange: (status) => {
                const statusEl = document.getElementById('header-conn-status');
                if (statusEl) statusEl.textContent = status.label;
            },
        });

        await window.peerManager.loadIceConfig();

        if (window.Echo) {
            try {
                const channel = window.Echo.join(`room.${currentRoomId}`);
                window.peerManager.signalSender = (targetUserId, signal) => {
                    fetch(`/api/rooms/${currentRoomId}/signal`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ target_user_id: targetUserId, type: signal.type, signal }),
                    }).catch(() => {
                        channel.whisper('signal', { from: currentUserId, to: targetUserId, signal });
                    });
                };

                channel.here((users) => {
                    const countEl = document.getElementById('members-count');
                    if (countEl) countEl.textContent = users.length;
                    users.forEach((u) => {
                        if (u.id !== currentUserId && currentUserId < u.id) window.peerManager.initiateOffer(u.id);
                    });
                });

                channel.joining((u) => {
                    const countEl = document.getElementById('members-count');
                    if (countEl) countEl.textContent = parseInt(countEl.textContent || '1') + 1;
                    if (u.id !== currentUserId && currentUserId < u.id) window.peerManager.initiateOffer(u.id);
                });

                channel.leaving((u) => {
                    const countEl = document.getElementById('members-count');
                    if (countEl) countEl.textContent = Math.max(1, parseInt(countEl.textContent || '1') - 1);
                    if (u && u.id) window.peerManager.removePeer(u.id);
                });

                channel.listen('.webrtc.signal', (data) => {
                    if (data.to === currentUserId && window.peerManager) window.peerManager.handleSignal(data.from, data.signal);
                });

                channel.listenForWhisper('signal', (data) => {
                    if (data.to === currentUserId && window.peerManager) window.peerManager.handleSignal(data.from, data.signal);
                });

                channel.listen('.broadcast.message', (data) => {
                    if (data.senderId !== currentUserId) renderIncomingMessage(data);
                });

                channel.listen('.room.participant.updated', (data) => {
                    if (data.action === 'kicked' && data.user_id === currentUserId) {
                        window.peerManager?.destroy();
                        alert('Anda telah dikeluarkan dari room oleh Admin.');
                        window.location.href = '{{ route("dashboard") }}';
                    } else if (data.action === 'locked' || data.action === 'unlocked') {
                        location.reload();
                    }
                });

                window.addEventListener('securelink:ws-state', (e) => {
                    if (e.detail?.state === 'connected') window.peerManager.handleTransportReconnect();
                });
            } catch (e) {}
        }

        window.addEventListener('beforeunload', () => window.peerManager?.destroy());
    });

    function toggleRoomLock() {
        fetch(`/api/rooms/${currentRoomId}/lock`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(res => res.json())
        .then(data => { if (data.success) location.reload(); else window.slToast?.(data.message || 'Gagal mengubah status lock', 'error'); });
    }

    function kickUser(targetUserId, targetName) {
        window.slConfirm({
            title: 'Keluarkan Peserta',
            message: `Apakah Anda yakin ingin mengeluarkan ${targetName} dari room ini?`,
            confirmLabel: 'Keluarkan',
            cancelLabel: 'Batal',
            onConfirm: () => {
                fetch(`/api/rooms/${currentRoomId}/kick`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ target_user_id: targetUserId }),
                })
                .then(res => res.json())
                .then(data => { if (data.success) location.reload(); else window.slToast?.(data.message || 'Gagal mengeluarkan peserta', 'error'); });
            }
        });
    }
</script>
@endsection
