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
                        $isArchived = $room->isLocked();
                    @endphp
                    <a href="{{ route('rooms.show', $room->room_id) }}"
                       class="room-item flex items-center justify-between gap-2.5 px-3.5 py-3 hover:bg-[var(--sl-hover)] transition"
                       data-name="{{ strtolower($room->room_name) }}"
                       data-id="{{ strtolower($room->room_id) }}"
                       data-type="{{ $isGroup ? 'group' : 'private' }}"
                       data-archived="{{ $isArchived ? '1' : '0' }}"
                       data-unread="0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full {{ $isGroup ? 'bg-[var(--sl-secondary-teal)]' : 'bg-[var(--sl-primary-teal)]' }} text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center uppercase">
                                    {{ strtoupper(substr($room->room_name, 0, $isGroup ? 2 : 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-[var(--sl-success)] ring-2 ring-[var(--sl-conv-bg)]"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">
                                    @if($isGroup)
                                        {{ $room->active_participants_count }} anggota &middot; {{ $room->room_id }}
                                    @else
                                        Private P2P &middot; {{ $room->room_id }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[10px] text-[var(--sl-text-secondary)]">{{ $part->last_seen_at?->format('H:i') }}</div>
                            @if($isArchived)
                                <span class="text-[10px] text-[var(--sl-accent-coral)] font-semibold">Locked</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif
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
    let currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';

    function setNavFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-pill').forEach(btn => {
            const active = btn.getAttribute('data-filter') === filter;
            btn.className = active
                ? 'filter-pill px-2.5 py-1 rounded-md bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] transition'
                : 'filter-pill px-2.5 py-1 rounded-md text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)] transition';
        });
        filterRooms();
    }
    window.setNavFilter = setNavFilter;

    function filterRooms() {
        const query = (document.getElementById('room-search')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.room-item').forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const id = item.getAttribute('data-id') || '';
            const type = item.getAttribute('data-type') || 'private';
            const archived = item.getAttribute('data-archived') === '1';
            const unread = item.getAttribute('data-unread') === '1';

            const matchesQuery = name.includes(query) || id.includes(query);
            let matchesFilter = true;
            if (currentFilter === 'private') matchesFilter = (type === 'private');
            else if (currentFilter === 'group') matchesFilter = (type === 'group');
            else if (currentFilter === 'archived') matchesFilter = archived;
            else if (currentFilter === 'unread') matchesFilter = unread;

            item.style.display = (matchesQuery && matchesFilter) ? 'flex' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (currentFilter !== 'all') setNavFilter(currentFilter);
    });
</script>
@endsection
