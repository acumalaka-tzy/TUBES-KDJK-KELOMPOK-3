@extends('layouts.app')

@section('title', 'Chats — SecureLink')

@section('app_shell')
<div class="h-screen w-screen overflow-hidden flex bg-[var(--sl-page-bg)] text-[var(--sl-text-primary)]">

    <!-- A. Slim Icon Navigation Sidebar -->
    @include('partials.app-nav')

    <!-- B. Conversation Sidebar -->
    <section class="w-full md:w-80 lg:w-96 border-r border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex flex-col shrink-0 h-full">
        <!-- Header: Brand, User, Single New Chat Button -->
        <div class="px-4 py-3 border-b border-[var(--sl-divider)] flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-0.5 shrink-0" style="background-color: {{ $user->avatar_color }}">
                    {!! $user->avatar_html !!}
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-[var(--sl-text-primary)] truncate">SecureLink</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">Halo, {{ $user->display_name }}!</div>
                </div>
            </div>

            <button type="button" onclick="openNewChatModal('menu')" title="Buat Room Baru"
                class="px-3 py-1.5 rounded-lg sl-btn-coral text-xs font-semibold flex items-center gap-1.5 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>New Chat</span>
            </button>
        </div>

        <!-- Search & Filter Tabs -->
        <div class="px-3 py-2.5 border-b border-[var(--sl-divider)] space-y-2 shrink-0">
            <input type="text" id="room-search" placeholder="Cari percakapan atau Room ID..." oninput="filterRooms()"
                class="w-full px-3 py-1.5 rounded-lg sl-input text-xs">

            <div class="flex items-center gap-1 text-[11px] font-semibold">
                <button type="button" onclick="setNavFilter('all')" data-filter="all" class="filter-pill px-2.5 py-1 rounded-md bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition">All</button>
                <button type="button" onclick="setNavFilter('private')" data-filter="private" class="filter-pill px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Private</button>
                <button type="button" onclick="setNavFilter('group')" data-filter="group" class="filter-pill px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Groups</button>
                <button type="button" onclick="setNavFilter('unread')" data-filter="unread" class="filter-pill px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition">Unread</button>
            </div>
        </div>

        <!-- Conversation List (Flat List Layout) -->
        <div id="conversation-list" class="flex-1 overflow-y-auto sl-scroll divide-y divide-[var(--sl-divider)]/50">
            @if($participations->isEmpty())
                <div class="p-6 text-center space-y-1.5">
                    <div class="text-xs font-semibold text-[var(--sl-text-primary)]">Belum ada percakapan</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Tekan New Chat untuk memulai.</div>
                </div>
            @else
                @foreach($participations as $part)
                    @php
                        $room = $part->room;
                        $isGroup = $room->max_participants > 2;
                        $summary = $roomSummaries[$room->room_id] ?? ['is_archived' => (bool) $part->is_archived, 'unread_count' => 0, 'last_message_preview' => null, 'last_message_time' => $part->last_seen_at?->format('H:i')];
                        $isArchived = (bool) ($summary['is_archived'] ?? false);
                        $unreadCount = (int) ($summary['unread_count'] ?? 0);
                        $unreadLabel = $unreadCount > 99 ? '99+' : (string) $unreadCount;
                        $previewText = $summary['last_message_preview'] ?: ($isGroup ? ($room->active_participants_count . ' anggota · ' . $room->room_id) : ('Private P2P · ' . $room->room_id));
                        $timeText = $summary['last_message_time'] ?: $part->last_seen_at?->format('H:i');
                    @endphp
                    <div class="room-item relative group flex items-center justify-between gap-2 px-3.5 py-3 hover:bg-[var(--sl-hover)] transition"
                         data-room-id="{{ $room->room_id }}"
                         data-name="{{ strtolower($room->room_name) }}"
                         data-id="{{ strtolower($room->room_id) }}"
                         data-type="{{ $isGroup ? 'group' : 'private' }}"
                         data-archived="{{ $isArchived ? '1' : '0' }}"
                         data-unread="{{ $unreadCount }}">
                        <a href="{{ route('rooms.show', $room->room_id) }}" class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full {{ $isGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center uppercase">
                                    {{ strtoupper(substr($room->room_name, 0, $isGroup ? 2 : 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-[var(--sl-success)] ring-2 ring-[var(--sl-conv-bg)]" title="Online"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="room-title text-xs {{ $unreadCount > 0 ? 'font-semibold' : 'font-medium' }} text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</div>
                                <div class="room-preview text-[11px] {{ $unreadCount > 0 ? 'font-semibold text-[var(--sl-text-primary)]' : 'text-[var(--sl-text-secondary)]' }} truncate">
                                    {{ $previewText }}
                                </div>
                            </div>
                        </a>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <div class="flex flex-col items-end gap-1">
                                <div class="room-time text-[10px] {{ $unreadCount > 0 ? 'font-semibold text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)]' }}">{{ $timeText }}</div>
                                <span class="unread-badge {{ $unreadCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] text-[10px] font-bold leading-none">{{ $unreadLabel }}</span>
                            </div>
                            <div class="relative">
                                <button type="button" onclick="toggleConvMenu(event, '{{ $room->room_id }}')" class="p-1 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-active-conv)] transition" title="Opsi percakapan" aria-label="Opsi percakapan">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/></svg>
                                </button>
                                <div id="conv-menu-{{ $room->room_id }}" class="conv-context-menu hidden absolute right-0 top-7 w-44 rounded-lg bg-[var(--sl-conv-bg)] border border-[var(--sl-divider)] shadow-lg py-1 z-50">
                                    <button type="button" onclick="toggleArchiveConversation(event, '{{ $room->room_id }}')" class="archive-toggle-btn w-full text-left px-3 py-1.5 text-xs text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)] transition">
                                        {{ $isArchived ? 'Keluarkan dari arsip' : 'Arsipkan percakapan' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Archived Empty State -->
            <div id="archived-empty-state" class="hidden p-8 text-center space-y-2">
                <div class="w-10 h-10 rounded-xl bg-[var(--sl-hover)] text-[var(--sl-text-secondary)] flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>
                    </svg>
                </div>
                <div class="text-xs font-semibold text-[var(--sl-text-primary)]">Belum ada percakapan yang diarsipkan</div>
                <div class="text-[11px] text-[var(--sl-text-secondary)]">Percakapan yang diarsipkan akan muncul di sini.</div>
            </div>
        </div>
    </section>

    <!-- C. Minimalist Empty State Area -->
    <main class="hidden md:flex flex-1 bg-[var(--sl-chat-bg)] flex-col items-center justify-center p-8 text-center h-full">
        <div class="max-w-sm space-y-4">
            <div class="w-12 h-12 rounded-xl bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <h1 class="text-lg font-bold text-[var(--sl-text-primary)]">Mulai Percakapan Terenkripsi</h1>
            <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                Pilih percakapan dari daftar atau mulai komunikasi peer-to-peer baru yang diamankan dengan WebRTC DataChannel dan enkripsi ujung-ke-ujung.
            </p>
            <div class="pt-1">
                <button type="button" onclick="openNewChatModal('menu')" class="px-5 py-2.5 rounded-lg sl-btn-coral text-xs font-semibold">
                    New Chat
                </button>
            </div>
        </div>
    </main>
</div>

@include('partials.new-chat-modal')

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const currentUserId = @json($user->user_id);
    const initialRoomSummaries = @json($roomSummaries);
    let currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';

    async function resolveEnvelopePreview(roomId, envelope, fallbackText) {
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

    function setNavFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-pill').forEach(btn => {
            const active = btn.getAttribute('data-filter') === filter;
            btn.className = active
                ? 'filter-pill px-2.5 py-1 rounded-md bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition'
                : 'filter-pill px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition';
        });
        window.updateNavActiveState?.(filter);
        filterRooms();
    }
    window.setNavFilter = setNavFilter;

    function filterRooms() {
        const query = (document.getElementById('room-search')?.value || '').toLowerCase().trim();
        let visibleCount = 0;

        document.querySelectorAll('.room-item').forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const id = item.getAttribute('data-id') || '';
            const type = item.getAttribute('data-type') || 'private';
            const archived = item.getAttribute('data-archived') === '1';
            const unreadCount = parseInt(item.getAttribute('data-unread') || '0', 10);

            const matchesQuery = name.includes(query) || id.includes(query);
            let matchesFilter = true;
            if (currentFilter === 'archived') {
                matchesFilter = archived;
            } else if (archived) {
                matchesFilter = false;
            } else if (currentFilter === 'private') {
                matchesFilter = (type === 'private');
            } else if (currentFilter === 'group') {
                matchesFilter = (type === 'group');
            } else if (currentFilter === 'unread') {
                matchesFilter = unreadCount > 0;
            }

            const show = matchesQuery && matchesFilter;
            item.style.display = show ? 'flex' : 'none';
            if (show) visibleCount++;
        });

        const archivedEmpty = document.getElementById('archived-empty-state');
        if (archivedEmpty) {
            archivedEmpty.classList.toggle('hidden', !(currentFilter === 'archived' && visibleCount === 0));
        }
    }

    function toggleConvMenu(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        document.querySelectorAll('.conv-context-menu').forEach(menu => {
            if (menu.id !== `conv-menu-${roomId}`) menu.classList.add('hidden');
        });
        document.getElementById(`conv-menu-${roomId}`)?.classList.toggle('hidden');
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.conv-context-menu').forEach(menu => menu.classList.add('hidden'));
    });

    function applyConversationUpdate(data) {
        if (!data || !data.room_id) return;
        const item = document.querySelector(`.room-item[data-room-id="${data.room_id}"]`);
        if (!item) return;

        const unreadCount = parseInt(data.unread_count ?? 0, 10);
        const isArchived = Boolean(data.is_archived);
        item.setAttribute('data-unread', String(unreadCount));
        item.setAttribute('data-archived', isArchived ? '1' : '0');

        const titleEl = item.querySelector('.room-title');
        const previewEl = item.querySelector('.room-preview');
        const timeEl = item.querySelector('.room-time');
        const badgeEl = item.querySelector('.unread-badge');
        const archiveBtn = item.querySelector('.archive-toggle-btn');

        if (titleEl) {
            titleEl.className = `room-title text-xs ${unreadCount > 0 ? 'font-semibold' : 'font-medium'} text-[var(--sl-text-primary)] truncate`;
        }
        if (previewEl) {
            if (data.last_message_envelope || data.last_message_preview) {
                resolveEnvelopePreview(data.room_id, data.last_message_envelope, data.last_message_preview).then((plain) => {
                    if (plain) previewEl.textContent = plain;
                });
            }
            previewEl.className = `room-preview text-[11px] ${unreadCount > 0 ? 'font-semibold text-[var(--sl-text-primary)]' : 'text-[var(--sl-text-secondary)]'} truncate`;
        }
        if (timeEl) {
            if (data.last_message_time) timeEl.textContent = data.last_message_time;
            timeEl.className = `room-time text-[10px] ${unreadCount > 0 ? 'font-semibold text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)]'}`;
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
            const list = document.getElementById('conversation-list');
            if (list && list.firstElementChild !== item) {
                list.insertBefore(item, list.firstElementChild);
            }
        }

        filterRooms();
    }

    function toggleArchiveConversation(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        const item = document.querySelector(`.room-item[data-room-id="${roomId}"]`);
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
                applyConversationUpdate({
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

    document.addEventListener('DOMContentLoaded', () => {
        setNavFilter(currentFilter);

        Object.entries(initialRoomSummaries || {}).forEach(([rId, summary]) => {
            const previewEl = document.querySelector(`.room-item[data-room-id="${rId}"] .room-preview`);
            if (previewEl && (summary.last_message_envelope || summary.last_message_preview)) {
                resolveEnvelopePreview(rId, summary.last_message_envelope, previewEl.textContent.trim()).then((plain) => {
                    if (plain) previewEl.textContent = plain;
                });
            }
        });

        if (window.Echo && currentUserId) {
            try {
                window.Echo.private(`user.${currentUserId}`)
                    .listen('.conversation.updated', (data) => applyConversationUpdate(data));
            } catch (e) {}
        }
    });
</script>
@endsection
