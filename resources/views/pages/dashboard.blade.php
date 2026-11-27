@extends('layouts.app')

@section('title', 'Chats — SecureLink')

@section('content')
@php
    $privateChats = $participations->filter(fn($p) => $p->room && $p->room->max_participants <= 2);
    $groupChats = $participations->filter(fn($p) => $p->room && $p->room->max_participants > 2);
@endphp

<div class="h-[calc(100vh-8.5rem)] min-h-[540px] flex rounded-xl overflow-hidden border border-[var(--sl-divider)] bg-[var(--sl-white-surface)]">

    <!-- A. Slim Primary Teal Navigation Rail -->
    <aside class="hidden sm:flex w-14 bg-[#497173] text-white flex-col items-center justify-between py-4 shrink-0">
        <div class="flex flex-col items-center gap-3">
            <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center" title="Chats">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </a>
            <a href="{{ route('rooms.create', ['type' => 'private']) }}" class="w-9 h-9 rounded-lg hover:bg-white/15 flex items-center justify-center transition" title="New Private Chat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </a>
            <a href="{{ route('rooms.create', ['type' => 'group']) }}" class="w-9 h-9 rounded-lg hover:bg-white/15 flex items-center justify-center transition" title="Create Group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
            <a href="{{ route('rooms.join') }}" class="w-9 h-9 rounded-lg hover:bg-white/15 flex items-center justify-center transition" title="Join Room / Scan QR">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </a>
        </div>

        <div class="flex flex-col items-center gap-3">
            <a href="{{ route('settings') }}" class="w-9 h-9 rounded-lg hover:bg-white/15 flex items-center justify-center transition" title="Settings &amp; Security Diagnostics">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
        </div>
    </aside>

    <!-- B. Conversation Sidebar (List Layout) -->
    <section class="w-full md:w-80 lg:w-96 border-r border-[var(--sl-divider)] bg-[var(--sl-white-surface)] flex flex-col shrink-0">
        <!-- User Profile Header -->
        <div class="p-3.5 border-b border-[var(--sl-divider)] flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-1 shrink-0" style="background-color: {{ $user->avatar_color }}">
                    {!! $user->avatar_html !!}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-[var(--sl-text-primary)] truncate">Halo, {{ $user->display_name }}!</div>
                    <div class="text-[11px] font-mono text-[var(--sl-text-secondary)] truncate">{{ $user->user_id }}</div>
                </div>
            </div>
            <a href="{{ route('settings') }}" class="text-[11px] font-semibold text-[var(--sl-primary-teal)] hover:underline shrink-0">Profil</a>
        </div>

        <!-- Search & Action Buttons -->
        <div class="p-3 border-b border-[var(--sl-divider)] space-y-2.5">
            <input type="text" id="room-search" placeholder="Cari percakapan atau Room ID..." oninput="filterRooms()"
                class="w-full px-3 py-2 rounded-lg sl-input text-xs">

            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('rooms.create', ['type' => 'private']) }}" class="px-2.5 py-1.5 rounded-lg sl-btn-primary text-[11px] font-semibold text-center">
                    + New Chat
                </a>
                <a href="{{ route('rooms.create', ['type' => 'group']) }}" class="px-2.5 py-1.5 rounded-lg sl-btn-secondary text-[11px] font-semibold text-center">
                    + Create Group
                </a>
            </div>
        </div>

        <!-- Categorized Conversation List -->
        <div class="flex-1 overflow-y-auto sl-scroll divide-y divide-[var(--sl-divider)]/60">
            @if($participations->isEmpty())
                <div class="p-6 text-center space-y-2">
                    <p class="text-xs font-semibold text-[var(--sl-text-primary)]">Belum ada percakapan</p>
                    <p class="text-[11px] text-[var(--sl-text-secondary)]">Mulai Private Chat, buat Group, atau gabung melalui QR Code.</p>
                </div>
            @else
                @if($privateChats->isNotEmpty())
                    <div class="py-1.5">
                        <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Private Chats</div>
                        @foreach($privateChats as $part)
                            @php $room = $part->room; @endphp
                            <a href="{{ route('rooms.show', $room->room_id) }}"
                               class="room-card flex items-center justify-between gap-2.5 px-3.5 py-2.5 hover:bg-[var(--sl-main-surface)] transition"
                               data-name="{{ strtolower($room->room_name) }}" data-id="{{ strtolower($room->room_id) }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-[#497173] text-white text-xs font-bold flex items-center justify-center shrink-0 uppercase">
                                        {{ strtoupper(substr($room->room_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</div>
                                        <div class="text-[11px] text-[var(--sl-text-secondary)] truncate font-mono">{{ $room->room_id }} &middot; 1-on-1</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-[10px] text-[var(--sl-text-secondary)]">{{ $part->last_seen_at?->format('H:i') }}</div>
                                    <span class="inline-block w-2 h-2 rounded-full bg-[#3B8A72] mt-1"></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($groupChats->isNotEmpty())
                    <div class="py-1.5">
                        <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Group Chats</div>
                        @foreach($groupChats as $part)
                            @php $room = $part->room; @endphp
                            <a href="{{ route('rooms.show', $room->room_id) }}"
                               class="room-card flex items-center justify-between gap-2.5 px-3.5 py-2.5 hover:bg-[var(--sl-main-surface)] transition"
                               data-name="{{ strtolower($room->room_name) }}" data-id="{{ strtolower($room->room_id) }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-[#71999A] text-white text-xs font-bold flex items-center justify-center shrink-0 uppercase">
                                        {{ strtoupper(substr($room->room_name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">{{ $room->room_name }}</div>
                                        <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">
                                            {{ $room->active_participants_count }} / {{ $room->max_participants }} anggota
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-[10px] text-[var(--sl-text-secondary)]">{{ $part->last_seen_at?->format('H:i') }}</div>
                                    @if($room->isLocked())
                                        <span class="text-[10px] text-[#EC643E] font-semibold">Locked</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- C. Empty / Welcome Workspace Area -->
    <main class="hidden md:flex flex-1 bg-[var(--sl-main-surface)] flex-col items-center justify-center p-8 text-center">
        <div class="max-w-md space-y-4">
            <div class="w-12 h-12 rounded-xl bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-lg font-bold text-[var(--sl-text-primary)]">SecureLink P2P Workspace</h1>
            <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                Pilih percakapan di sebelah kiri atau mulai ruang komunikasi terenkripsi baru melalui WebRTC DataChannel.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5">
                <a href="{{ route('rooms.create', ['type' => 'private']) }}" class="px-4 py-2 rounded-lg sl-btn-primary text-xs font-semibold">New Private Chat</a>
                <a href="{{ route('rooms.create', ['type' => 'group']) }}" class="px-4 py-2 rounded-lg sl-btn-secondary text-xs font-semibold">Buat Room Baru</a>
                <a href="{{ route('rooms.join') }}" class="px-4 py-2 rounded-lg sl-btn-coral text-xs font-semibold">Gabung / Scan QR</a>
            </div>
        </div>
    </main>

</div>

@push('scripts')
<script>
    function filterRooms() {
        const query = document.getElementById('room-search').value.toLowerCase().trim();
        document.querySelectorAll('.room-card').forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const id = card.getAttribute('data-id') || '';
            card.style.display = (name.includes(query) || id.includes(query)) ? 'flex' : 'none';
        });
    }
</script>
@endpush
@endsection
