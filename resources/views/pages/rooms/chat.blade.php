@extends('layouts.app')

@section('title', $room->room_name . ' — SecureLink')

@section('app_shell')
@php
    $isGroup = $room->max_participants > 2;
    $allRooms = isset($userRooms) ? $userRooms : collect();
@endphp

<div class="sl-app-shell h-screen w-screen overflow-hidden flex bg-[var(--sl-page-bg)] text-[var(--sl-text-primary)] relative">

    <!-- A. Slim Icon Navigation Sidebar -->
    @include('partials.app-nav')

    <!-- B. Conversation Sidebar -->
    <aside id="chat-sidebar" class="w-full md:w-72 lg:w-80 border-r border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex-col shrink-0 md:flex hidden absolute md:static inset-0 z-40 h-full min-h-0">
        <!-- Header: Brand, User, Single New Chat Button -->
        <div class="px-3.5 py-3 border-b border-[var(--sl-divider)] flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-0.5 shrink-0" style="background-color: {{ $user->avatar_color }}">
                    {!! $user->avatar_html !!}
                </div>
                <span class="text-xs font-bold text-[var(--sl-text-primary)] truncate">{{ $user->display_name }}</span>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" onclick="openNewChatModal('menu')" class="px-2.5 py-1.5 md:py-1 rounded-lg sl-btn-coral text-[11px] font-semibold flex items-center gap-1 min-h-[36px] md:min-h-0">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>New Chat</span>
                </button>
                <button type="button" onclick="toggleChatSidebar()" class="md:hidden min-w-[40px] min-h-[40px] inline-flex items-center justify-center rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] font-bold text-lg" title="Tutup">&times;</button>
            </div>
        </div>

        <!-- Search & Filter Tabs -->
        <div class="px-3 py-2 border-b border-[var(--sl-divider)] space-y-2 shrink-0">
            <input type="text" id="sidebar-search" placeholder="Cari percakapan atau Room ID..." oninput="filterSidebarRooms()"
                class="w-full px-3 py-1.5 rounded-lg sl-input text-base md:text-xs">

            <div class="flex items-center gap-1.5 md:gap-1 text-[11px] font-semibold overflow-x-auto sl-no-scrollbar">
                <button type="button" onclick="setSidebarFilter('all')" data-sfilter="all" class="sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition whitespace-nowrap shrink-0">All</button>
                <button type="button" onclick="setSidebarFilter('private')" data-sfilter="private" class="sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition whitespace-nowrap shrink-0">Private</button>
                <button type="button" onclick="setSidebarFilter('group')" data-sfilter="group" class="sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition whitespace-nowrap shrink-0">Groups</button>
                <button type="button" onclick="setSidebarFilter('unread')" data-sfilter="unread" class="sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition whitespace-nowrap shrink-0">Unread</button>
                <button type="button" onclick="setSidebarFilter('archived')" data-sfilter="archived" class="md:hidden sfilter-pill px-2.5 py-1 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition whitespace-nowrap shrink-0">Archived</button>
            </div>
        </div>

        <!-- Conversation List -->
        <div id="sidebar-rooms-list" class="flex-1 overflow-y-auto sl-scroll divide-y divide-[var(--sl-divider)]/50">
            @forelse($allRooms as $uPart)
                @php
                    $uRoom = $uPart->room;
                    $isActive = ($uRoom->room_id === $room->room_id);
                    $uIsGroup = $uRoom->max_participants > 2;
                    $uSummary = $roomSummaries[$uRoom->room_id] ?? ['is_archived' => (bool) $uPart->is_archived, 'unread_count' => 0, 'last_message_preview' => null, 'last_message_time' => $uPart->last_seen_at?->format('H:i')];
                    $uArchived = (bool) ($uSummary['is_archived'] ?? false);
                    $uUnread = (int) ($uSummary['unread_count'] ?? 0);
                    $uUnreadLabel = $uUnread > 99 ? '99+' : (string) $uUnread;
                    $uPreview = $uSummary['last_message_preview'] ?: (($uIsGroup ? ($uRoom->active_participants_count ?? 1) . ' anggota' : 'Private P2P') . ' · ' . $uRoom->room_id);
                    $uTime = $uSummary['last_message_time'] ?: $uPart->last_seen_at?->format('H:i');
                @endphp
                <div class="sidebar-item relative group flex items-center justify-between gap-2 px-3.5 py-2.5 transition {{ $isActive ? 'bg-[var(--sl-active-conv)]' : 'hover:bg-[var(--sl-hover)]' }}"
                     data-room-id="{{ $uRoom->room_id }}"
                     data-name="{{ strtolower($uRoom->room_name) }}"
                     data-id="{{ strtolower($uRoom->room_id) }}"
                     data-type="{{ $uIsGroup ? 'group' : 'private' }}"
                     data-archived="{{ $uArchived ? '1' : '0' }}"
                     data-unread="{{ $uUnread }}">
                    <a href="{{ route('rooms.show', $uRoom->room_id) }}" class="flex items-center gap-2.5 min-w-0 flex-1">
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-full {{ $uIsGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center uppercase">
                                {{ strtoupper(substr($uRoom->room_name, 0, $uIsGroup ? 2 : 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-[var(--sl-success)] ring-2 ring-[var(--sl-conv-bg)]" title="Online"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="sidebar-room-title text-xs {{ $uUnread > 0 ? 'font-semibold' : ($isActive ? 'font-semibold' : 'font-medium') }} truncate {{ $isActive ? 'text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-primary)]' }}">{{ $uRoom->room_name }}</div>
                            <div class="sidebar-room-preview text-[11px] {{ $uUnread > 0 ? 'font-semibold text-[var(--sl-text-primary)]' : 'text-[var(--sl-text-secondary)]' }} truncate">
                                {{ $uPreview }}
                            </div>
                        </div>
                    </a>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="flex flex-col items-end gap-1">
                            <span class="sidebar-room-time text-[10px] {{ $uUnread > 0 ? 'font-semibold text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)]' }}">{{ $uTime }}</span>
                            <span class="sidebar-unread-badge {{ $uUnread > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] text-[10px] font-bold leading-none">{{ $uUnreadLabel }}</span>
                        </div>
                        <div class="relative">
                            <button type="button" onclick="toggleSidebarConvMenu(event, '{{ $uRoom->room_id }}')" class="p-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)] transition" title="Opsi percakapan" aria-label="Opsi percakapan">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/></svg>
                            </button>
                            <div id="sidebar-conv-menu-{{ $uRoom->room_id }}" class="sidebar-conv-menu hidden absolute right-0 top-6 w-44 rounded-lg bg-[var(--sl-conv-bg)] border border-[var(--sl-divider)] shadow-lg py-1 z-50">
                                <button type="button" onclick="toggleSidebarArchive(event, '{{ $uRoom->room_id }}')" class="sidebar-archive-btn w-full text-left px-3 py-1.5 text-xs text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)] transition">
                                    {{ $uArchived ? 'Keluarkan dari arsip' : 'Arsipkan percakapan' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-xs text-[var(--sl-text-secondary)]">
                    Room aktif: <strong class="text-[var(--sl-text-primary)]">{{ $room->room_name }}</strong>
                </div>
            @endforelse

            <div id="sidebar-archived-empty" class="hidden p-6 text-center space-y-2">
                <div class="w-9 h-9 rounded-xl bg-[var(--sl-hover)] text-[var(--sl-text-secondary)] flex items-center justify-center mx-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>
                    </svg>
                </div>
                <div class="text-xs font-semibold text-[var(--sl-text-primary)]">Belum ada percakapan yang diarsipkan</div>
                <div class="text-[11px] text-[var(--sl-text-secondary)]">Percakapan yang diarsipkan akan muncul di sini.</div>
            </div>
        </div>
    </aside>

    <!-- C. Main Conversation Area -->
    <main class="flex-1 flex flex-col min-w-0 min-h-0 bg-[var(--sl-chat-bg)] relative h-full">

        <!-- Conversation Header -->
        <header class="sticky top-0 z-20 px-2.5 sm:px-5 py-2 sm:py-2.5 border-b border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex items-center justify-between gap-2 shrink-0 relative">
            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0 flex-1">
                <a href="{{ route('dashboard') }}" class="md:hidden min-w-[40px] min-h-[40px] inline-flex items-center justify-center rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] shrink-0" aria-label="Kembali ke daftar percakapan" title="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <div class="w-9 h-9 rounded-full {{ $isGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center shrink-0 uppercase">
                    {{ strtoupper(substr($room->room_name, 0, $isGroup ? 2 : 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                        <h1 class="text-sm font-bold text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</h1>
                        <span class="hidden sm:inline text-[10px] px-1.5 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] font-semibold shrink-0">
                            {{ $isGroup ? 'Group' : 'Private' }}
                        </span>
                        @if($room->isLocked())
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-[var(--sl-accent-coral)]/15 text-[var(--sl-accent-coral)] font-semibold shrink-0">Terkunci</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--sl-text-secondary)] mt-0.5 min-w-0">
                        <span class="hidden sm:inline font-mono truncate">{{ $room->room_id }}</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span id="header-conn-status" class="font-semibold text-[var(--sl-primary-teal)] truncate">Menunggu Peer...</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Action Overflow Button -->
            <div class="md:hidden flex items-center shrink-0 relative">
                <button type="button" id="mobile-chat-menu-btn" onclick="toggleMobileChatMenu(event)" class="min-w-[40px] min-h-[40px] inline-flex items-center justify-center rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)]" aria-label="Menu percakapan" aria-expanded="false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.2" fill="currentColor"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/><circle cx="12" cy="19" r="1.2" fill="currentColor"/></svg>
                </button>

                <div id="mobile-chat-menu" class="hidden absolute right-0 top-12 w-60 rounded-xl sl-surface shadow-xl border border-[var(--sl-divider)] py-1.5 z-50 text-xs">
                    <button type="button" onclick="toggleMembersDrawer(); toggleMobileChatMenu(false)" class="w-full px-3.5 py-2.5 text-left flex items-center justify-between hover:bg-[var(--sl-hover)] text-[var(--sl-text-primary)] font-medium">
                        <span class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[var(--sl-primary-teal)]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Info &amp; Anggota</span>
                        </span>
                        <span id="mobile-members-count" class="px-1.5 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] text-[10px] font-bold">{{ count($metadata['participants']) }}</span>
                    </button>

                    <button type="button" onclick="openInviteModal(); toggleMobileChatMenu(false)" class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 hover:bg-[var(--sl-hover)] text-[var(--sl-text-primary)] font-medium">
                        <svg class="w-4 h-4 text-[var(--sl-primary-teal)]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/><path d="M17 17h4v4h-4z"/></svg>
                        <span>QR Code &amp; Undangan</span>
                    </button>

                    <button type="button" onclick="setCommunicationMode(communicationMode === 'p2p' ? 'broadcast' : 'p2p'); toggleMobileChatMenu(false)" class="w-full px-3.5 py-2.5 text-left flex items-center justify-between hover:bg-[var(--sl-hover)] text-[var(--sl-text-primary)] font-medium">
                        <span>Mode Koneksi</span>
                        <span id="mobile-mode-label" class="px-1.5 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] text-[10px] font-bold">P2P WebRTC</span>
                    </button>

                    <button type="button" onclick="toggleChatSidebar(); toggleMobileChatMenu(false)" class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 hover:bg-[var(--sl-hover)] text-[var(--sl-text-primary)] font-medium">
                        <svg class="w-4 h-4 text-[var(--sl-text-secondary)]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
                        <span>Daftar Percakapan</span>
                    </button>

                    @if($metadata['is_creator'] && !empty($metadata['current_dynamic_pin']))
                        <div class="mx-3 my-1 px-2.5 py-1.5 rounded-lg bg-[var(--sl-elevated)] border border-[var(--sl-divider)] flex items-center justify-between text-[11px]">
                            <span class="text-[var(--sl-text-secondary)]">Dynamic TOTP</span>
                            <span class="font-mono font-bold text-[var(--sl-primary-teal)]">{{ $metadata['current_dynamic_pin'] }}</span>
                        </div>
                    @endif

                    <div class="my-1 border-t border-[var(--sl-divider)]"></div>

                    <form action="{{ route('rooms.leave.web', $room->room_id) }}" method="POST" onsubmit="event.preventDefault(); toggleMobileChatMenu(false); window.slConfirm({ title: 'Keluar Room', message: 'Apakah Anda yakin ingin keluar dari room ini?', confirmLabel: 'Keluar Room', cancelLabel: 'Batal', onConfirm: () => this.submit() });">
                        @csrf
                        <button type="submit" class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 text-[var(--sl-accent-coral)] hover:bg-[var(--sl-accent-coral)]/10 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Keluar Room</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Desktop Header Controls (Unchanged) -->
            <div class="hidden md:flex items-center gap-2 shrink-0">
                <!-- P2P vs Broadcast Mode Toggle -->
                <div class="flex items-center p-0.5 rounded-lg bg-[var(--sl-chat-bg)] border border-[var(--sl-divider)] text-[11px] font-semibold">
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
                    <span>Undang</span>
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
        <div id="messages-container" class="flex-1 min-h-0 overflow-y-auto overscroll-y-contain sl-scroll p-3 sm:p-5 space-y-3">
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
        <div id="reply-bar" class="hidden px-3 sm:px-4 py-2 bg-[var(--sl-active-conv)] border-t border-[var(--sl-divider)] flex items-center justify-between gap-2 text-xs shrink-0">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="text-[var(--sl-primary-teal)] font-bold shrink-0">Membalas:</span>
                <span id="reply-author" class="font-semibold text-[var(--sl-text-primary)] shrink-0"></span>
                <span id="reply-text" class="text-[var(--sl-text-secondary)] truncate min-w-0"></span>
            </div>
            <button type="button" onclick="cancelReply()" class="min-w-[32px] min-h-[32px] inline-flex items-center justify-center text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] font-bold text-base shrink-0">&times;</button>
        </div>

        <!-- Message Composer -->
        <div class="relative px-2.5 py-2 sm:p-3 sl-safe-bottom md:pb-3 border-t border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] shrink-0">
            <!-- Quick Emoji Picker Popover -->
            <div id="emoji-popover" class="hidden absolute bottom-full mb-2 left-2 right-2 sm:right-auto sm:left-3 z-30 p-2 rounded-lg sl-surface shadow-md flex flex-wrap items-center gap-1.5 text-base max-w-[calc(100vw-1rem)]">
                @foreach(['👍', '❤️', '🔒', '✅', '😂', '🎉', '🔥', '👋'] as $em)
                    <button type="button" onclick="insertEmoji('{{ $em }}')" class="min-w-[36px] min-h-[36px] sm:min-w-0 sm:min-h-0 p-1.5 sm:p-1 rounded hover:bg-[var(--sl-hover)] inline-flex items-center justify-center">{{ $em }}</button>
                @endforeach
            </div>

            <form id="message-form" onsubmit="handleSendMessage(event)" class="flex items-end gap-1.5 sm:gap-2">
                <button type="button" onclick="toggleEmojiPicker()" class="min-w-[40px] min-h-[40px] inline-flex items-center justify-center p-2 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] shrink-0" title="Emoji">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                </button>

                <select id="ephemeral_duration" aria-label="Durasi Pesan Ephemeral" title="Pesan Sementara (Ephemeral)"
                    class="max-w-[78px] sm:max-w-none px-1.5 sm:px-2 py-2 min-h-[40px] rounded-lg sl-input text-xs shrink-0">
                    <option value="0">Normal</option>
                    <option value="10">10 dtk</option>
                    <option value="30">30 dtk</option>
                    <option value="60">1 mnt</option>
                    <option value="300">5 mnt</option>
                </select>

                <textarea id="message-input" rows="1" aria-label="Tulis pesan" placeholder="Tulis pesan..." autocomplete="off"
                    class="flex-1 min-w-0 px-3 sm:px-3.5 py-2 min-h-[40px] max-h-28 overflow-y-auto sl-scroll rounded-lg sl-input text-base md:text-sm leading-snug resize-none"></textarea>

                <button type="submit" class="min-h-[40px] min-w-[44px] px-3 sm:px-4 py-2 rounded-lg sl-btn-coral font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 shrink-0">
                    <span class="hidden sm:inline">Kirim</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        </div>
    </main>

    <!-- D. Collapsible Right Information Panel -->
    <aside id="members-drawer" class="hidden absolute md:static top-0 right-0 bottom-0 w-full sm:w-80 border-l border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex flex-col z-40 md:z-30 overflow-y-auto sl-scroll shrink-0 h-full min-h-0">
        <div class="p-4 border-b border-[var(--sl-divider)] flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--sl-text-primary)]">
                {{ $isGroup ? 'Info Grup & Keamanan' : 'Info Percakapan' }}
            </h2>
            <button type="button" onclick="toggleMembersDrawer()" class="min-w-[36px] min-h-[36px] md:min-w-0 md:min-h-0 inline-flex items-center justify-center rounded-lg text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)] text-base font-bold">&times;</button>
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
    const CHAT_HISTORY_KEY = `slk_history_${currentRoomId}_${currentUserId}`;

    function loadChatHistory() {
        try {
            const raw = localStorage.getItem(CHAT_HISTORY_KEY);
            if (!raw) return [];
            const now = Date.now();
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) return [];
            const valid = parsed.filter(m => !m.expiresAt || m.expiresAt > now);
            if (valid.length !== parsed.length) {
                localStorage.setItem(CHAT_HISTORY_KEY, JSON.stringify(valid));
            }
            return valid;
        } catch (e) {
            return [];
        }
    }

    function saveChatMessageToHistory(entry) {
        try {
            const items = loadChatHistory();
            if (!items.some(m => m.id === entry.id)) {
                items.push(entry);
                localStorage.setItem(CHAT_HISTORY_KEY, JSON.stringify(items.slice(-150)));
            }
        } catch (e) {}
    }

    function updateChatHistoryItem(msgId, updater) {
        try {
            const items = loadChatHistory();
            const idx = items.findIndex(m => m.id === msgId);
            if (idx !== -1) {
                items[idx] = updater(items[idx]);
                localStorage.setItem(CHAT_HISTORY_KEY, JSON.stringify(items));
            }
        } catch (e) {}
    }

    function removeChatMessageFromHistory(msgId) {
        try {
            const items = loadChatHistory().filter(m => m.id !== msgId);
            localStorage.setItem(CHAT_HISTORY_KEY, JSON.stringify(items));
        } catch (e) {}
    }

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

    function toggleMobileChatMenu(forceState) {
        const menu = document.getElementById('mobile-chat-menu');
        const btn = document.getElementById('mobile-chat-menu-btn');
        if (!menu) return;
        if (typeof forceState === 'object' && forceState && typeof forceState.stopPropagation === 'function') {
            forceState.stopPropagation();
            forceState = undefined;
        }
        const shouldOpen = typeof forceState === 'boolean' ? forceState : menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !shouldOpen);
        btn?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    }
    window.toggleMobileChatMenu = toggleMobileChatMenu;

    document.addEventListener('click', (e) => {
        const menu = document.getElementById('mobile-chat-menu');
        const btn = document.getElementById('mobile-chat-menu-btn');
        if (!menu || menu.classList.contains('hidden')) return;
        if (!menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
            toggleMobileChatMenu(false);
        }
    });

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

    function autoResizeComposerInput() {
        const input = document.getElementById('message-input');
        if (!input) return;
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 112) + 'px';
    }

    function isNearMessagesBottom(threshold = 96) {
        const container = document.getElementById('messages-container');
        if (!container) return true;
        return (container.scrollHeight - container.scrollTop - container.clientHeight) <= threshold;
    }

    function scrollMessagesToBottom(force = false) {
        const container = document.getElementById('messages-container');
        if (!container) return;
        if (force || isNearMessagesBottom()) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('message-input');
        if (input) {
            input.value += emoji;
            autoResizeComposerInput();
            input.focus();
        }
        document.getElementById('emoji-popover')?.classList.add('hidden');
    }

    (() => {
        const composerInput = document.getElementById('message-input');
        if (!composerInput) return;

        composerInput.addEventListener('input', autoResizeComposerInput);
        composerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                const enterSendEnabled = localStorage.getItem('securelink_enter_send') !== 'false';
                if (enterSendEnabled) {
                    e.preventDefault();
                    document.getElementById('message-form')?.requestSubmit();
                }
            }
        });
        composerInput.addEventListener('focus', () => {
            const wasNearBottom = isNearMessagesBottom();
            if (wasNearBottom) {
                setTimeout(() => scrollMessagesToBottom(true), 220);
            }
        });

        window.addEventListener('securelink:keyboard-resize', (e) => {
            if (e.detail?.keyboardOpen && isNearMessagesBottom(140)) {
                requestAnimationFrame(() => scrollMessagesToBottom(true));
            }
        });
    })();

    function setSidebarFilter(filter) {
        currentSidebarFilter = filter;
        document.querySelectorAll('.sfilter-pill').forEach(btn => {
            const active = btn.getAttribute('data-sfilter') === filter;
            const mobileOnlyClass = btn.getAttribute('data-sfilter') === 'archived' ? 'md:hidden ' : '';
            btn.className = active
                ? `${mobileOnlyClass}sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition whitespace-nowrap shrink-0`
                : `${mobileOnlyClass}sfilter-pill px-2.5 py-1 md:px-2 md:py-0.5 rounded text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition whitespace-nowrap shrink-0`;
        });
        window.updateNavActiveState?.(filter);
        filterSidebarRooms();
    }
    window.setNavFilter = setSidebarFilter;

    function filterSidebarRooms() {
        const query = (document.getElementById('sidebar-search')?.value || '').toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('.sidebar-item').forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const id = item.getAttribute('data-id') || '';
            const type = item.getAttribute('data-type') || 'private';
            const archived = item.getAttribute('data-archived') === '1';
            const unreadCount = parseInt(item.getAttribute('data-unread') || '0', 10);

            const matchesQuery = name.includes(query) || id.includes(query);
            let matchesFilter = true;
            if (currentSidebarFilter === 'archived') {
                matchesFilter = archived;
            } else if (archived) {
                matchesFilter = false;
            } else if (currentSidebarFilter === 'private') {
                matchesFilter = (type === 'private');
            } else if (currentSidebarFilter === 'group') {
                matchesFilter = (type === 'group');
            } else if (currentSidebarFilter === 'unread') {
                matchesFilter = unreadCount > 0;
            }

            const show = matchesQuery && matchesFilter;
            item.style.display = show ? 'flex' : 'none';
            if (show) visibleCount++;
        });

        const archivedEmpty = document.getElementById('sidebar-archived-empty');
        if (archivedEmpty) {
            archivedEmpty.classList.toggle('hidden', !(currentSidebarFilter === 'archived' && visibleCount === 0));
        }
    }

    function toggleSidebarConvMenu(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        document.querySelectorAll('.sidebar-conv-menu').forEach(menu => {
            if (menu.id !== `sidebar-conv-menu-${roomId}`) menu.classList.add('hidden');
        });
        document.getElementById(`sidebar-conv-menu-${roomId}`)?.classList.toggle('hidden');
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.sidebar-conv-menu').forEach(menu => menu.classList.add('hidden'));
    });

    const initialSidebarSummaries = @json($roomSummaries ?? []);

    async function resolveSidebarEnvelopePreview(roomId, envelope, fallbackText) {
        if (envelope && envelope.ciphertext) {
            try {
                if (envelope.transport === 'broadcast' && !envelope.encrypted && !envelope.iv) {
                    return envelope.ciphertext;
                }
                const fromB64 = (b64) => {
                    const bin = atob(b64);
                    const bytes = new Uint8Array(bin.length);
                    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
                    return bytes;
                };
                if (!envelope.iv || envelope.encrypted === false) {
                    return new TextDecoder().decode(fromB64(envelope.ciphertext));
                }
                if (window.crypto?.subtle) {
                    const enc = new TextEncoder();
                    const keyMat = await window.crypto.subtle.digest('SHA-256', enc.encode(`securelink-room-envelope:${roomId}`));
                    const key = await window.crypto.subtle.importKey('raw', keyMat, { name: 'AES-GCM' }, false, ['decrypt']);
                    const aad = enc.encode(`${roomId}|${envelope.sender_id}|${envelope.id}`);
                    const plainBuf = await window.crypto.subtle.decrypt(
                        { name: 'AES-GCM', iv: fromB64(envelope.iv), additionalData: aad },
                        key,
                        fromB64(envelope.ciphertext)
                    );
                    return new TextDecoder().decode(plainBuf);
                }
            } catch (e) {}
        }

        try {
            const raw = localStorage.getItem(`securelink_chat_${roomId}_${currentUserId}`);
            if (raw) {
                const items = JSON.parse(raw).filter(m => !m.deleted && (!m.expiresAt || m.expiresAt > Date.now()));
                if (items.length > 0 && items[items.length - 1].text) {
                    return items[items.length - 1].text;
                }
            }
        } catch (e) {}

        return fallbackText || 'Pesan terenkripsi';
    }

    function applySidebarConversationUpdate(data) {
        if (!data || !data.room_id) return;
        const item = document.querySelector(`.sidebar-item[data-room-id="${data.room_id}"]`);
        if (!item) return;

        const unreadCount = parseInt(data.unread_count ?? 0, 10);
        const isArchived = Boolean(data.is_archived);
        item.setAttribute('data-unread', String(unreadCount));
        item.setAttribute('data-archived', isArchived ? '1' : '0');

        const titleEl = item.querySelector('.sidebar-room-title');
        const previewEl = item.querySelector('.sidebar-room-preview');
        const timeEl = item.querySelector('.sidebar-room-time');
        const badgeEl = item.querySelector('.sidebar-unread-badge');
        const archiveBtn = item.querySelector('.sidebar-archive-btn');
        const isActiveRoom = data.room_id === currentRoomId;

        if (titleEl) {
            titleEl.className = `sidebar-room-title text-xs ${unreadCount > 0 || isActiveRoom ? 'font-semibold' : 'font-medium'} truncate ${isActiveRoom ? 'text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-primary)]'}`;
        }
        if (previewEl) {
            if (data.last_message_envelope || data.last_message_preview) {
                resolveSidebarEnvelopePreview(data.room_id, data.last_message_envelope, data.last_message_preview).then((plain) => {
                    if (plain) previewEl.textContent = plain;
                });
            }
            previewEl.className = `sidebar-room-preview text-[11px] ${unreadCount > 0 ? 'font-semibold text-[var(--sl-text-primary)]' : 'text-[var(--sl-text-secondary)]'} truncate`;
        }
        if (timeEl) {
            if (data.last_message_time) timeEl.textContent = data.last_message_time;
            timeEl.className = `sidebar-room-time text-[10px] ${unreadCount > 0 ? 'font-semibold text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)]'}`;
        }
        if (badgeEl) {
            badgeEl.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            badgeEl.classList.toggle('hidden', unreadCount <= 0);
            badgeEl.classList.toggle('inline-flex', unreadCount > 0);
        }
        if (archiveBtn) {
            archiveBtn.textContent = isArchived ? 'Keluarkan dari arsip' : 'Arsipkan percakapan';
        }

        if (data.action === 'message') {
            const list = document.getElementById('sidebar-rooms-list');
            if (list && list.firstElementChild !== item) {
                list.insertBefore(item, list.firstElementChild);
            }
        }

        filterSidebarRooms();
    }

    function toggleSidebarArchive(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        const item = document.querySelector(`.sidebar-item[data-room-id="${roomId}"]`);
        const isArchived = item?.getAttribute('data-archived') === '1';
        const endpoint = isArchived ? `/api/rooms/${roomId}/unarchive` : `/api/rooms/${roomId}/archive`;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                applySidebarConversationUpdate({
                    room_id: roomId,
                    unread_count: parseInt(item?.getAttribute('data-unread') || '0', 10),
                    is_archived: Boolean(data.is_archived),
                    action: data.is_archived ? 'archived' : 'unarchived',
                });
                window.slToast?.(data.is_archived ? 'Percakapan diarsipkan.' : 'Percakapan dikeluarkan dari arsip.', 'success');
            }
        })
        .catch(() => window.slToast?.('Gagal memperbarui status arsip.', 'error'));
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
            .map(em => `<button type="button" onclick="reactToMessage('${escapeHtml(msgId)}', '${em}'); closeAllBubbleToolbars();" class="text-base sm:text-lg leading-none px-1 py-0.5 rounded-full hover:bg-[var(--sl-hover)] hover:scale-115 active:scale-110 transition">${em}</button>`)
            .join('');
        const posClass = isOwn ? 'right-2' : 'left-2';
        return `<div class="msg-toolbar absolute -top-5 ${posClass} hidden group-hover:flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-xs sm:text-[13px] shadow-md z-20 whitespace-nowrap max-w-[92vw] overflow-x-auto">
            ${emojis}
            <span class="text-[var(--sl-divider)]">|</span>
            <button type="button" onclick="handleReplyFromBubble(this, '${escapeHtml(msgId)}'); closeAllBubbleToolbars();" class="px-1.5 py-0.5 rounded hover:bg-[var(--sl-hover)] hover:text-[var(--sl-primary-teal)] font-semibold transition" title="Balas">Balas</button>
            <button type="button" onclick="copyMessageText(this); closeAllBubbleToolbars();" class="px-1.5 py-0.5 rounded hover:bg-[var(--sl-hover)] hover:text-[var(--sl-primary-teal)] font-semibold transition" title="Salin">Salin</button>
            <button type="button" onclick="deleteMessage('${escapeHtml(msgId)}', ${isOwn ? 'true' : 'false'}); closeAllBubbleToolbars();" class="px-1.5 py-0.5 rounded hover:bg-[var(--sl-hover)] text-[var(--sl-accent-coral)] font-semibold transition" title="Hapus">Hapus</button>
        </div>`;
    }

    function closeAllBubbleToolbars() {
        document.querySelectorAll('.group.sl-toolbar-open').forEach(el => {
            el.classList.remove('sl-toolbar-open');
            const tb = el.querySelector('.msg-toolbar');
            if (tb) tb.classList.add('hidden');
        });
    }
    window.closeAllBubbleToolbars = closeAllBubbleToolbars;

    document.addEventListener('click', (e) => {
        const clickedToolbar = e.target.closest('.msg-toolbar');
        if (clickedToolbar) return;

        const msgBubble = e.target.closest('[data-message-id] .msg-content');
        if (msgBubble) {
            const groupEl = msgBubble.closest('.group');
            const toolbar = groupEl?.querySelector('.msg-toolbar');
            if (groupEl && toolbar) {
                const wasOpen = groupEl.classList.contains('sl-toolbar-open');
                closeAllBubbleToolbars();
                if (!wasOpen) {
                    groupEl.classList.add('sl-toolbar-open');
                    toolbar.classList.remove('hidden');
                    toolbar.classList.add('flex');
                }
            }
            return;
        }

        closeAllBubbleToolbars();
    });

    const messageReactionsMap = new Map();

    function renderMessageReactionPills(msgId) {
        const wrapper = document.querySelector(`[data-message-id="${msgId}"]`);
        if (!wrapper) return;
        const container = wrapper.querySelector('.reaction-pills');
        if (!container) return;

        const emojiMap = messageReactionsMap.get(msgId) || {};
        container.innerHTML = '';

        let hasAny = false;
        Object.entries(emojiMap).forEach(([emoji, userSet]) => {
            if (!userSet || userSet.size === 0) return;
            hasAny = true;
            const reactedByMe = userSet.has(currentUserId);
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.dataset.emoji = emoji;
            pill.onclick = () => reactToMessage(msgId, emoji);
            pill.className = reactedByMe
                ? 'px-1.5 py-0.5 rounded-full bg-[var(--sl-active-conv)] border border-[var(--sl-primary-teal)] text-[10px] flex items-center gap-1 text-[var(--sl-primary-teal)]'
                : 'px-1.5 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[10px] flex items-center gap-1 text-[var(--sl-text-primary)]';
            pill.innerHTML = `<span>${emoji}</span><span class="rx-count font-bold">${userSet.size}</span>`;
            container.appendChild(pill);
        });

        container.classList.toggle('hidden', !hasAny);
    }

    function reactToMessage(msgId, emoji) {
        const emojiMap = messageReactionsMap.get(msgId) || {};
        const alreadyReacted = emojiMap[emoji]?.has(currentUserId) || false;
        const action = alreadyReacted ? 'remove' : 'add';

        applyMessageReaction(msgId, emoji, currentUserId, action);
        if (window.peerManager) {
            window.peerManager.sendReaction(msgId, emoji, action);
        }
    }
    window.reactToMessage = reactToMessage;

    function applyMessageReaction(msgId, emoji, senderId = currentUserId, action = 'add', skipPersist = false) {
        if (!msgId || !emoji) return;
        let emojiMap = messageReactionsMap.get(msgId);
        if (!emojiMap) {
            emojiMap = {};
            messageReactionsMap.set(msgId, emojiMap);
        }
        if (!emojiMap[emoji]) {
            emojiMap[emoji] = new Set();
        }

        const actorId = senderId || 'peer';
        if (action === 'remove') {
            emojiMap[emoji].delete(actorId);
        } else {
            emojiMap[emoji].add(actorId);
        }

        renderMessageReactionPills(msgId);

        if (!skipPersist) {
            updateChatHistoryItem(msgId, (item) => {
                const reactionUsers = {};
                const reactions = {};
                Object.entries(emojiMap).forEach(([em, set]) => {
                    if (set.size > 0) {
                        reactionUsers[em] = Array.from(set);
                        reactions[em] = set.size;
                    }
                });
                return { ...item, reactions, reactionUsers };
            });
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
        updateChatHistoryItem(msgId, (item) => ({ ...item, deleted: true }));
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
        const mobileModeLabel = document.getElementById('mobile-mode-label');

        if (communicationMode === 'broadcast') {
            if (p2pBtn) p2pBtn.className = 'px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
            if (bcastBtn) bcastBtn.className = 'px-2.5 py-1 rounded-md bg-[var(--sl-accent-coral)] text-white transition';
            if (mobileModeLabel) {
                mobileModeLabel.textContent = 'Broadcast';
                mobileModeLabel.className = 'px-1.5 py-0.5 rounded bg-[var(--sl-accent-coral)]/15 text-[var(--sl-accent-coral)] text-[10px] font-bold';
            }
            if (bannerText) bannerText.textContent = 'Broadcast Comparison Mode aktif • Pesan diteruskan melalui Server Reverb (Non-P2P)';
        } else {
            if (p2pBtn) p2pBtn.className = 'px-2.5 py-1 rounded-md bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] transition';
            if (bcastBtn) bcastBtn.className = 'px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
            if (mobileModeLabel) {
                mobileModeLabel.textContent = 'P2P WebRTC';
                mobileModeLabel.className = 'px-1.5 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] text-[10px] font-bold';
            }
            if (bannerText) bannerText.textContent = 'P2P WebRTC DataChannel aktif • E2EE AES-GCM • Server tidak menyimpan pesan';
        }
    }

    const STATUS_RANK = { failed: 0, sending: 1, queued: 2, sent: 3, delivered: 4, read: 5 };
    const unreadIncomingMessages = new Map();
    const visibleIncomingIds = new Set();
    let incomingVisibilityObserver = null;

    function normalizeMessageStatus(item) {
        if (!item) return 'sent';
        if (item.status && STATUS_RANK[item.status] !== undefined) return item.status;
        if (Array.isArray(item.readBy) && item.readBy.length > 0) return 'read';
        if (item.delivered || (Array.isArray(item.ackedBy) && item.ackedBy.length > 0)) return 'delivered';
        return 'sent';
    }

    function renderStatusBadge(status, messageId, meta = {}) {
        const readCount = Array.isArray(meta.readBy) ? meta.readBy.length : 0;
        const ackCount = Array.isArray(meta.ackedBy) ? meta.ackedBy.length : 0;
        const groupSuffix = (isGroupRoom && status === 'read' && readCount > 0)
            ? ` (${readCount})`
            : (isGroupRoom && status === 'delivered' && ackCount > 0 ? ` (${ackCount})` : '');

        switch (status) {
            case 'sending':
                return `<span class="inline-flex items-center gap-1 text-[var(--sl-text-secondary)] font-medium" title="Sedang mengirim pesan">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
                    <span>Mengirim...</span>
                </span>`;
            case 'queued':
                return `<span class="inline-flex items-center gap-1 text-[var(--sl-text-secondary)] font-medium" title="Pesan tersimpan aman, menunggu penerima membuka percakapan">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
                    <span>Menunggu penerima</span>
                </span>`;
            case 'sent':
                return `<span class="inline-flex items-center gap-1 text-[var(--sl-text-secondary)] font-semibold" title="Terkirim">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 13 10 18 20 7"></polyline></svg>
                    <span>Terkirim</span>
                </span>`;
            case 'delivered':
                return `<span class="inline-flex items-center gap-1 text-[#7D8B8D] dark:text-[#98A6A8] font-semibold" title="${isGroupRoom && ackCount > 0 ? `Diterima oleh ${ackCount} anggota` : 'Diterima perangkat penerima'}">
                    <svg class="w-4 h-3.5 shrink-0" viewBox="0 0 28 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 13 7 18 17 7"></polyline><polyline points="9 13 14 18 24 7"></polyline></svg>
                    <span>Diterima${groupSuffix}</span>
                </span>`;
            case 'read':
                return `<span class="inline-flex items-center gap-1 text-[var(--sl-primary-teal)] font-semibold" title="${isGroupRoom && readCount > 0 ? `Dibaca oleh ${readCount} anggota` : 'Dibaca oleh penerima'}">
                    <svg class="w-4 h-3.5 shrink-0" viewBox="0 0 28 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 13 7 18 17 7"></polyline><polyline points="9 13 14 18 24 7"></polyline></svg>
                    <span>Dibaca${groupSuffix}</span>
                </span>`;
            case 'failed':
                return `<span class="inline-flex items-center gap-1.5 text-[var(--sl-accent-coral)] font-semibold" title="Pengiriman pesan gagal">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><circle cx="12" cy="16" r="0.8" fill="currentColor"></circle></svg>
                    <span>Gagal dikirim</span>
                    <button type="button" onclick="retryChatMessage('${messageId}')" class="underline hover:opacity-80 cursor-pointer font-bold">Coba lagi</button>
                </span>`;
            default:
                return `<span class="inline-flex items-center gap-1 text-[var(--sl-text-secondary)] font-semibold">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 13 10 18 20 7"></polyline></svg>
                    <span>Terkirim</span>
                </span>`;
        }
    }

    function updateMessageStatus(messageId, nextStatus, meta = {}) {
        if (!messageId || STATUS_RANK[nextStatus] === undefined) return;
        const bubble = document.getElementById('bubble_' + messageId) || document.querySelector(`[data-message-id="${messageId}"]`);
        const currentStatus = bubble?.dataset.status || 'sending';
        const allowDowngrade = Boolean(meta.allowDowngrade);

        if (!allowDowngrade) {
            if (nextStatus === 'failed') {
                if (currentStatus === 'queued' || currentStatus === 'delivered' || currentStatus === 'read') return;
            } else if (STATUS_RANK[nextStatus] < STATUS_RANK[currentStatus]) {
                return;
            }
        }

        let mergedAckedBy = Array.isArray(meta.ackedBy) ? [...new Set(meta.ackedBy)] : null;
        let mergedReadBy = Array.isArray(meta.readBy) ? [...new Set(meta.readBy)] : null;

        updateChatHistoryItem(messageId, (item) => {
            const ackSet = new Set(Array.isArray(item.ackedBy) ? item.ackedBy : []);
            const readSet = new Set(Array.isArray(item.readBy) ? item.readBy : []);
            if (meta.ackUserId) ackSet.add(meta.ackUserId);
            if (meta.readUserId) {
                ackSet.add(meta.readUserId);
                readSet.add(meta.readUserId);
            }
            if (mergedAckedBy) mergedAckedBy.forEach((id) => ackSet.add(id));
            if (mergedReadBy) mergedReadBy.forEach((id) => readSet.add(id));

            mergedAckedBy = Array.from(ackSet);
            mergedReadBy = Array.from(readSet);

            return {
                ...item,
                status: nextStatus,
                delivered: nextStatus === 'delivered' || nextStatus === 'read' || Boolean(item.delivered),
                ackedBy: mergedAckedBy,
                readBy: mergedReadBy,
            };
        });

        if (bubble) {
            bubble.dataset.status = nextStatus;
            const badge = bubble.querySelector('.delivery-badge');
            if (badge) {
                badge.innerHTML = renderStatusBadge(nextStatus, messageId, {
                    ackedBy: mergedAckedBy || meta.ackedBy || [],
                    readBy: mergedReadBy || meta.readBy || [],
                });
            }
        }
    }

    function sendBroadcastReceipt(messageId, status) {
        if (!messageId || !['delivered', 'read'].includes(status)) return;
        fetch(`/api/rooms/${currentRoomId}/receipt`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Socket-Id': window.Echo?.socketId?.() || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message_id: messageId, status }),
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data && data.success && status === 'read' && typeof data.unread_count === 'number') {
                applySidebarConversationUpdate({
                    room_id: currentRoomId,
                    unread_count: data.unread_count,
                    is_archived: document.querySelector(`.sidebar-item[data-room-id="${currentRoomId}"]`)?.getAttribute('data-archived') === '1',
                    action: 'read',
                });
            }
        })
        .catch(() => {});
    }

    function ensureIncomingObserver() {
        if (incomingVisibilityObserver) return incomingVisibilityObserver;
        const container = document.getElementById('messages-container');
        if (!container || typeof IntersectionObserver === 'undefined') return null;

        incomingVisibilityObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const msgId = entry.target?.dataset?.messageId;
                if (!msgId) return;
                if (entry.isIntersecting) {
                    visibleIncomingIds.add(msgId);
                } else {
                    visibleIncomingIds.delete(msgId);
                }
            });
            flushVisibleReadReceipts();
        }, { root: container, threshold: 0.2 });

        return incomingVisibilityObserver;
    }

    function isElementVisibleInContainer(el, container) {
        if (!el || !container) return false;
        const cRect = container.getBoundingClientRect();
        const eRect = el.getBoundingClientRect();
        if (eRect.height === 0 && eRect.width === 0) return false;
        return eRect.bottom > cRect.top && eRect.top < cRect.bottom;
    }

    function flushVisibleReadReceipts() {
        if (document.visibilityState !== 'visible' || !document.hasFocus()) return;
        if (unreadIncomingMessages.size === 0) return;

        const container = document.getElementById('messages-container');
        const p2pIds = [];
        const allReadIds = [];

        for (const [msgId, entry] of Array.from(unreadIncomingMessages.entries())) {
            const isVisible = visibleIncomingIds.has(msgId) || isElementVisibleInContainer(entry.el, container);
            if (!isVisible) continue;

            allReadIds.push(msgId);
            if (entry.transport !== 'broadcast') {
                p2pIds.push(msgId);
            }
            unreadIncomingMessages.delete(msgId);
            visibleIncomingIds.delete(msgId);
            if (incomingVisibilityObserver && entry.el) {
                incomingVisibilityObserver.unobserve(entry.el);
            }
            updateChatHistoryItem(msgId, (item) => ({ ...item, readSent: true }));
        }

        if (p2pIds.length > 0 && window.peerManager) {
            window.peerManager.sendReadReceipt(p2pIds);
        }
        allReadIds.forEach((id) => sendBroadcastReceipt(id, 'read'));
    }

    window.addEventListener('focus', flushVisibleReadReceipts);
    document.addEventListener('visibilitychange', flushVisibleReadReceipts);

    window.retryChatMessage = function(messageId) {
        const history = loadChatHistory();
        const item = history.find((m) => m.id === messageId && m.isOwn);
        if (!item) return;

        updateMessageStatus(messageId, 'sending', { allowDowngrade: true });

        if (item.transport === 'broadcast') {
            fetch(`/api/rooms/${currentRoomId}/broadcast-message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Socket-Id': window.Echo?.socketId?.() || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message: item.text,
                    message_id: messageId,
                    ephemeral_duration: item.ephemeralDuration || 0,
                    reply_to: item.replyTo || null,
                }),
            }).then((res) => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                updateMessageStatus(messageId, 'sent');
            }).catch(() => {
                updateMessageStatus(messageId, 'failed');
            });
        } else if (window.peerManager) {
            window.peerManager.retryMessage(messageId).then((found) => {
                if (!found) {
                    window.peerManager.broadcastMessage(item.text, item.ephemeralDuration || 0, item.replyTo || null, messageId);
                }
            });
        }
    };

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
        bubbleWrapper.dataset.status = 'sending';
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
                    <span class="delivery-badge">${renderStatusBadge('sending', clientMsgId)}</span>
                    ${duration > 0 ? `<span class="countdown-badge text-[var(--sl-accent-coral)] font-semibold">&middot; ⏱ ${duration}s</span>` : ''}
                </div>
            </div>
        `;

        list.appendChild(bubbleWrapper);
        input.value = '';
        autoResizeComposerInput();
        scrollMessagesToBottom(true);

        const expiresAt = duration > 0 ? Date.now() + (duration * 1000) : null;
        saveChatMessageToHistory({
            id: clientMsgId,
            isOwn: true,
            senderId: currentUserId,
            senderName: 'Anda',
            text,
            timestamp: timeStr,
            replyTo: replyPayload,
            transport: communicationMode,
            status: 'sending',
            ackedBy: [],
            readBy: [],
            expiresAt,
            reactions: {},
        });

        if (duration > 0) {
            let remaining = duration;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const timer = setInterval(() => {
                remaining--;
                if (badge) badge.textContent = `· ⏱ ${remaining}s`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    removeChatMessageFromHistory(bubbleWrapper.dataset.messageId || clientMsgId);
                    bubbleWrapper.style.transition = 'opacity 0.35s ease';
                    bubbleWrapper.style.opacity = '0';
                    setTimeout(() => bubbleWrapper.remove(), 350);
                }
            }, 1000);
        }

        if (communicationMode === 'broadcast') {
            fetch(`/api/rooms/${currentRoomId}/broadcast-message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Socket-Id': window.Echo?.socketId?.() || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text, message_id: clientMsgId, ephemeral_duration: duration, reply_to: replyPayload }),
            })
            .then((res) => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                updateMessageStatus(clientMsgId, 'sent');
            })
            .catch((err) => {
                console.error('[SecureLink] Broadcast error:', err);
                updateMessageStatus(clientMsgId, 'failed');
            });
        } else if (window.peerManager) {
            window.peerManager.broadcastMessage(text, duration, replyPayload, clientMsgId);
        } else {
            updateMessageStatus(clientMsgId, 'failed');
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
                        : ''}
                    ${data.ephemeralDuration > 0 ? `<span class="countdown-badge text-[var(--sl-accent-coral)] font-semibold">&middot; ⏱ ${data.ephemeralDuration}s</span>` : ''}
                </div>
            </div>
        `;

        const shouldAutoScroll = !data._restored && isNearMessagesBottom();
        list.appendChild(bubbleWrapper);
        if (shouldAutoScroll) {
            scrollMessagesToBottom(true);
        }

        const activeSidebarPreview = document.querySelector(`.sidebar-item[data-room-id="${currentRoomId}"] .sidebar-room-preview`);
        if (activeSidebarPreview && data.text) {
            activeSidebarPreview.textContent = data.text;
        }

        if (!data._restored) {
            const expiresAt = data.ephemeralDuration > 0 ? Date.now() + (data.ephemeralDuration * 1000) : null;
            saveChatMessageToHistory({
                id: data.id,
                isOwn: false,
                senderId: data.senderId,
                senderName: data.senderName,
                senderAvatarColor: data.senderAvatarColor || '#497173',
                senderAvatarIcon: data.senderAvatarIcon || 'initial',
                text: data.text,
                timestamp: data.timestamp,
                ephemeralDuration: data.ephemeralDuration || 0,
                replyTo: data.replyTo || null,
                transport: data.transport || 'p2p',
                readSent: false,
                expiresAt,
                reactions: {},
            });
        }

        if (!data.readSent && data.id) {
            unreadIncomingMessages.set(data.id, {
                id: data.id,
                transport: data.transport || 'p2p',
                el: bubbleWrapper,
            });
            const obs = ensureIncomingObserver();
            if (obs) {
                obs.observe(bubbleWrapper);
            }
            requestAnimationFrame(() => flushVisibleReadReceipts());
        }

        if (data.ephemeralDuration > 0) {
            let remaining = data.ephemeralDuration;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const timer = setInterval(() => {
                remaining--;
                if (badge) badge.textContent = `· ⏱ ${remaining}s`;
                if (remaining <= 0) {
                    clearInterval(timer);
                    removeChatMessageFromHistory(data.id);
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

    function renderRestoredOwnMessage(item) {
        const list = document.getElementById('messages-list');
        const bubbleWrapper = document.createElement('div');
        bubbleWrapper.id = 'bubble_' + item.id;
        bubbleWrapper.dataset.messageId = item.id;
        bubbleWrapper.className = 'flex justify-end group';

        const remainingSec = item.expiresAt ? Math.max(1, Math.ceil((item.expiresAt - Date.now()) / 1000)) : 0;
        let replyHtml = '';
        if (item.replyTo) {
            replyHtml = `<div class="mb-1.5 px-2.5 py-1.5 rounded bg-[var(--sl-elevated)]/80 border-l-2 border-[var(--sl-primary-teal)] sl-chat-reply">
                <div class="font-bold text-[var(--sl-primary-teal)]">${escapeHtml(item.replyTo.authorName)}</div>
                <div class="truncate text-[var(--sl-text-secondary)]">${escapeHtml(item.replyTo.text)}</div>
            </div>`;
        }

        const contentHtml = item.deleted
            ? `<div class="msg-content italic text-[var(--sl-text-secondary)]">Pesan ini telah dihapus.</div>`
            : `<div class="msg-content break-words whitespace-pre-wrap select-text">${escapeHtml(item.text)}</div>${buildBubbleToolbar(item.id, true)}`;

        const restoredStatus = normalizeMessageStatus(item);
        bubbleWrapper.dataset.status = restoredStatus;

        bubbleWrapper.innerHTML = `
            <div class="flex flex-col items-end max-w-[82%] sm:max-w-[68%]">
                <span class="sender-name hidden">Anda</span>
                <div class="relative px-3.5 py-2.5 rounded-xl rounded-tr-sm sl-chat-msg leading-relaxed" style="background-color: var(--sl-bubble-out); color: var(--sl-bubble-out-text);">
                    ${replyHtml}
                    ${contentHtml}
                </div>
                <div class="reaction-pills hidden flex flex-wrap gap-1 mt-1"></div>
                <div class="flex items-center gap-1.5 text-[10px] text-[var(--sl-text-secondary)] mt-1">
                    <span>${escapeHtml(item.timestamp || '')}</span>
                    <span class="delivery-badge">${renderStatusBadge(restoredStatus, item.id, { ackedBy: item.ackedBy || [], readBy: item.readBy || [] })}</span>
                    ${remainingSec > 0 ? `<span class="countdown-badge text-[var(--sl-accent-coral)] font-semibold">&middot; ⏱ ${remainingSec}s</span>` : ''}
                </div>
            </div>
        `;
        list.appendChild(bubbleWrapper);

        if (!item.deleted) {
            if (item.reactionUsers) {
                Object.entries(item.reactionUsers).forEach(([emoji, users]) => {
                    (users || []).forEach((uid) => applyMessageReaction(item.id, emoji, uid, 'add', true));
                });
            } else if (item.reactions) {
                Object.entries(item.reactions).forEach(([emoji, count]) => {
                    for (let i = 0; i < count; i++) applyMessageReaction(item.id, emoji, `restored_${i}`, 'add', true);
                });
            }
        }

        if (remainingSec > 0) {
            let rem = remainingSec;
            const badge = bubbleWrapper.querySelector('.countdown-badge');
            const t = setInterval(() => {
                rem--;
                if (badge) badge.textContent = `· ⏱ ${rem}s`;
                if (rem <= 0) {
                    clearInterval(t);
                    removeChatMessageFromHistory(item.id);
                    bubbleWrapper.remove();
                }
            }, 1000);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Restore local non-expired chat history
        const history = loadChatHistory();
        history.forEach(item => {
            if (item.isOwn) {
                renderRestoredOwnMessage(item);
            } else {
                const rem = item.expiresAt ? Math.max(1, Math.ceil((item.expiresAt - Date.now()) / 1000)) : 0;
                renderIncomingMessage({ ...item, ephemeralDuration: rem, _restored: true });
                if (item.deleted) applyMessageDeleted(item.id);
                else if (item.reactionUsers) {
                    Object.entries(item.reactionUsers).forEach(([emoji, users]) => {
                        (users || []).forEach((uid) => applyMessageReaction(item.id, emoji, uid, 'add', true));
                    });
                } else if (item.reactions) {
                    Object.entries(item.reactions).forEach(([emoji, count]) => {
                        for (let i = 0; i < count; i++) applyMessageReaction(item.id, emoji, `restored_${i}`, 'add', true);
                    });
                }
            }
        });
        scrollMessagesToBottom(true);
        requestAnimationFrame(() => scrollMessagesToBottom(true));

        @if(session('just_created_room'))
            openInviteModal();
        @endif

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
            onReactionReceived: (rx) => applyMessageReaction(rx.messageId, rx.emoji, rx.senderId, rx.action || 'add'),
            onMessageDeleted: (del) => applyMessageDeleted(del.messageId),
            onDiagnosticsUpdate: updateDiagnosticsUi,
            onMessageSent: (messageId) => {
                updateMessageStatus(messageId, 'sent');
            },
            onMessageQueued: (messageId) => {
                updateMessageStatus(messageId, 'queued');
            },
            onMessageAck: (messageId, ackUserId, ackedBy) => {
                updateMessageStatus(messageId, 'delivered', { ackUserId, ackedBy });
            },
            onMessageRead: (messageId, readUserId, readBy) => {
                updateMessageStatus(messageId, 'read', { readUserId, readBy });
            },
            onMessageFailed: (messageId) => {
                updateMessageStatus(messageId, 'failed');
            },
            pendingMessageSaver: async (payload) => {
                try {
                    const res = await fetch(`/api/rooms/${currentRoomId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Socket-Id': window.Echo?.socketId?.() || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    return res.ok;
                } catch (err) {
                    return false;
                }
            },
            onStatusChange: (status) => {
                const statusEl = document.getElementById('header-conn-status');
                if (statusEl) statusEl.textContent = status.label;
            },
        });

        await window.peerManager.loadIceConfig();

        const updateRoomPresence = (state, useBeacon = false) => {
            const url = `/api/rooms/${currentRoomId}/presence`;
            if (useBeacon && navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify({ state, _token: csrfToken })], { type: 'application/json' });
                navigator.sendBeacon(url, blob);
                return;
            }
            fetch(url, {
                method: 'POST',
                keepalive: useBeacon,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ state }),
            }).catch(() => {});
        };

        const syncPendingMessages = async () => {
            try {
                const res = await fetch(`/api/rooms/${currentRoomId}/pending-messages`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success || !Array.isArray(data.messages)) return;

                for (const msg of data.messages) {
                    const existingBubble = document.getElementById('bubble_peer_' + msg.id);
                    if (existingBubble) {
                        if (msg.receipt_status === 'pending') {
                            sendBroadcastReceipt(msg.id, 'delivered');
                        }
                        if (msg.receipt_status !== 'read') {
                            unreadIncomingMessages.set(msg.id, {
                                id: msg.id,
                                transport: msg.transport || 'p2p',
                                el: existingBubble,
                            });
                            const obs = ensureIncomingObserver();
                            if (obs) obs.observe(existingBubble);
                        }
                        continue;
                    }
                    let text = msg.ciphertext;
                    if (window.peerManager && (msg.transport !== 'broadcast' || msg.encrypted)) {
                        try {
                            text = await window.peerManager.decryptRoomEnvelope(
                                msg.ciphertext,
                                msg.iv,
                                msg.senderId,
                                msg.id,
                                msg.encrypted,
                                msg.transport || 'p2p'
                            );
                        } catch (e) {
                            text = '[Pesan terenkripsi — gagal mendekripsi]';
                        }
                    }
                    renderIncomingMessage({
                        id: msg.id,
                        senderId: msg.senderId,
                        senderName: msg.senderName,
                        senderAvatarColor: msg.senderAvatarColor,
                        senderAvatarIcon: msg.senderAvatarIcon,
                        text,
                        timestamp: msg.timestamp,
                        ephemeralDuration: msg.ephemeralDuration || 0,
                        replyTo: msg.replyTo || null,
                        transport: msg.transport || 'p2p',
                        readSent: false,
                    });
                    sendBroadcastReceipt(msg.id, 'delivered');
                }
                flushVisibleReadReceipts();
            } catch (e) {}
        };

        filterSidebarRooms();
        Object.entries(initialSidebarSummaries || {}).forEach(([rId, summary]) => {
            const previewEl = document.querySelector(`.sidebar-item[data-room-id="${rId}"] .sidebar-room-preview`);
            if (previewEl && (summary.last_message_envelope || summary.last_message_preview)) {
                resolveSidebarEnvelopePreview(rId, summary.last_message_envelope, previewEl.textContent.trim()).then((plain) => {
                    if (plain) previewEl.textContent = plain;
                });
            }
        });
        updateRoomPresence('in_conversation');
        syncPendingMessages();

        if (window.Echo) {
            try {
                if (currentUserId) {
                    window.Echo.private(`user.${currentUserId}`)
                        .listen('.conversation.updated', (data) => applySidebarConversationUpdate(data));
                }

                const channel = window.Echo.join(`room.${currentRoomId}`);
                window.peerManager.signalSender = (targetUserId, signal) => {
                    fetch(`/api/rooms/${currentRoomId}/signal`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Socket-Id': window.Echo?.socketId?.() || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ target_user_id: targetUserId, type: signal.type, signal }),
                    }).catch(() => {
                        channel.whisper('signal', { from: currentUserId, to: targetUserId, signal });
                    });
                };

                channel.here((users) => {
                    const countEl = document.getElementById('members-count');
                    const mobileCountEl = document.getElementById('mobile-members-count');
                    if (countEl) countEl.textContent = users.length;
                    if (mobileCountEl) mobileCountEl.textContent = users.length;
                    users.forEach((u) => {
                        if (u.id !== currentUserId && currentUserId < u.id) window.peerManager.initiateOffer(u.id);
                    });
                });

                channel.joining((u) => {
                    const countEl = document.getElementById('members-count');
                    const mobileCountEl = document.getElementById('mobile-members-count');
                    const nextCount = parseInt(countEl?.textContent || '1') + 1;
                    if (countEl) countEl.textContent = nextCount;
                    if (mobileCountEl) mobileCountEl.textContent = nextCount;
                    if (u.id !== currentUserId && currentUserId < u.id) window.peerManager.initiateOffer(u.id);
                });

                channel.leaving((u) => {
                    const countEl = document.getElementById('members-count');
                    const mobileCountEl = document.getElementById('mobile-members-count');
                    const nextCount = Math.max(1, parseInt(countEl?.textContent || '1') - 1);
                    if (countEl) countEl.textContent = nextCount;
                    if (mobileCountEl) mobileCountEl.textContent = nextCount;
                    if (u && u.id) window.peerManager.removePeer(u.id);
                });

                channel.listen('.webrtc.signal', (data) => {
                    if (data.to === currentUserId && window.peerManager) window.peerManager.handleSignal(data.from, data.signal);
                });

                channel.listenForWhisper('signal', (data) => {
                    if (data.to === currentUserId && window.peerManager) window.peerManager.handleSignal(data.from, data.signal);
                });

                channel.listen('.broadcast.message', (data) => {
                    if (data.senderId !== currentUserId) {
                        renderIncomingMessage(data);
                        sendBroadcastReceipt(data.id, 'delivered');
                    }
                });

                channel.listen('.message.receipt', (data) => {
                    if (!data || data.user_id === currentUserId) return;
                    if (data.status === 'delivered') {
                        updateMessageStatus(data.message_id, 'delivered', { ackUserId: data.user_id });
                    } else if (data.status === 'read') {
                        updateMessageStatus(data.message_id, 'read', { readUserId: data.user_id });
                    }
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
                    const st = e.detail?.state;
                    if (st === 'connected') {
                        window.peerManager.handleTransportReconnect();
                        updateRoomPresence('in_conversation');
                        syncPendingMessages();
                    } else if (st === 'disconnected' || st === 'unavailable') {
                        updateRoomPresence('reconnecting');
                    }
                });
            } catch (e) {}
        }

        window.addEventListener('beforeunload', () => {
            updateRoomPresence('online', true);
            window.peerManager?.destroy();
        });
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
