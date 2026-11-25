@extends('layouts.app')

@section('title', 'Dashboard — SecureLink')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto py-2">
    <!-- User Profile Banner (Glassmorphic) -->
    <div class="glass-card p-6 sm:p-7 rounded-3xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-center gap-4.5">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-extrabold uppercase shadow-lg shadow-blue-500/20 shrink-0 transform transition duration-300 hover:rotate-3" style="background-color: {{ $user->avatar_color }}">
                {{ substr($user->display_name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Halo, {{ $user->display_name }}!</h1>
                    <span class="inline-flex items-center gap-1.5 text-[10px] px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-[#55C49A] font-semibold border border-emerald-200/80 dark:border-emerald-800/60 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sesi Aktif
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-xs font-mono text-[#667085] dark:text-[#A8B2C3] bg-black/5 dark:bg-white/5 px-2 py-0.5 rounded-md">ID: {{ $user->user_id }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $user->user_id }}'); alert('User ID disalin!')" class="text-[11px] text-[#315CE8] dark:text-[#7895FF] font-semibold hover:underline">
                        Salin
                    </button>
                    <span class="text-[#667085] dark:text-[#A8B2C3]">&bull;</span>
                    <a href="{{ route('settings') }}" class="text-[11px] text-[#667085] dark:text-[#A8B2C3] hover:text-[#315CE8] dark:hover:text-[#7895FF]">
                        Edit Profil
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('rooms.create') }}" class="flex-1 sm:flex-none px-5 py-3 rounded-2xl bg-gradient-to-r from-[#315CE8] to-[#254BC5] hover:opacity-95 text-white font-semibold text-xs shadow-md shadow-blue-500/25 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Room Baru
            </a>
            <a href="{{ route('rooms.join') }}" class="flex-1 sm:flex-none px-5 py-3 rounded-2xl glass-card text-[#182033] dark:text-[#F1F4FA] font-semibold text-xs transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Gabung Room
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card p-5 rounded-3xl flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#315CE8] dark:text-[#7895FF] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-xs text-[#667085] dark:text-[#A8B2C3]">Total Room Sesi</div>
                <div class="text-lg font-extrabold text-[#182033] dark:text-[#F1F4FA]">{{ $participations->count() }} Room</div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-3xl flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-[#55C49A] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="text-xs text-[#667085] dark:text-[#A8B2C3]">Kriptografi P2P</div>
                <div class="text-lg font-extrabold text-[#182033] dark:text-[#F1F4FA]">AES-GCM 256</div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-3xl flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-[#7657D6] dark:text-[#A58AE8] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="text-xs text-[#667085] dark:text-[#A8B2C3]">Kanal Data</div>
                <div class="text-lg font-extrabold text-[#182033] dark:text-[#F1F4FA]">WebRTC Direct</div>
            </div>
        </div>
    </div>

    <!-- Active Conversations / Recent Rooms Section -->
    <div class="glass-card rounded-3xl p-6 sm:p-7 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-[#E3E7EF] dark:border-[#344052]">
            <div>
                <h2 class="text-base font-bold text-[#182033] dark:text-[#F1F4FA]">Daftar Room Obrolan</h2>
                <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">Room aktif yang Anda ikuti atau buat pada sesi ini</p>
            </div>

            <!-- Search input filter -->
            <div class="w-full sm:w-64">
                <input type="text" id="room-search" placeholder="Cari nama room atau ID..." oninput="filterRooms()"
                    class="w-full px-3.5 py-2 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#151A24]/80 text-xs text-[#182033] dark:text-[#F1F4FA] placeholder-[#667085]/60 dark:placeholder-[#A8B2C3]/50 focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF] transition">
            </div>
        </div>

        @if($participations->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-[#E3E7EF] dark:border-[#344052] rounded-3xl">
                <div class="w-14 h-14 rounded-3xl bg-[#EEF1F7] dark:bg-[#222B3A] text-[#667085] dark:text-[#A8B2C3] flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-sm font-semibold text-[#182033] dark:text-[#F1F4FA]">Belum ada obrolan room</h3>
                <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1 max-w-sm mx-auto">
                    Anda belum bergabung atau membuat room obrolan. Buat room baru atau minta Room ID dari teman Anda.
                </p>
                <div class="mt-5 flex items-center justify-center gap-2.5">
                    <a href="{{ route('rooms.create') }}" class="px-4 py-2 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white text-xs font-semibold shadow-sm transition">Buat Room</a>
                    <a href="{{ route('rooms.join') }}" class="px-4 py-2 rounded-xl glass-card text-[#182033] dark:text-[#F1F4FA] text-xs font-semibold transition">Gabung Room</a>
                </div>
            </div>
        @else
            <div id="rooms-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($participations as $part)
                    @php $room = $part->room; @endphp
                    <div class="room-card p-4.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white/60 dark:bg-[#222B3A]/40 hover:border-[#315CE8]/50 dark:hover:border-[#7895FF]/50 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between"
                        data-name="{{ strtolower($room->room_name) }}" data-id="{{ strtolower($room->room_id) }}">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="text-sm font-bold text-[#182033] dark:text-[#F1F4FA] truncate" title="{{ $room->room_name }}">
                                    {{ $room->room_name }}
                                </h3>
                                @if($part->isCreator())
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/70 text-[#315CE8] dark:text-[#7895FF] font-bold shrink-0">
                                        Creator
                                    </span>
                                @else
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-[#EEF1F7] dark:bg-[#2A3546] text-[#667085] dark:text-[#A8B2C3] font-medium shrink-0">
                                        Anggota
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs font-mono text-[#667085] dark:text-[#A8B2C3] mb-3">
                                <span class="bg-black/5 dark:bg-white/5 px-1.5 py-0.5 rounded">{{ $room->room_id }}</span>
                                @if($room->hasPin())
                                    <span class="text-amber-500 font-sans text-[11px]" title="Dilindungi PIN">🔒 PIN</span>
                                @endif
                                @if($room->isLocked())
                                    <span class="text-rose-500 font-sans text-[11px]" title="Room Dikunci">⛔ Terkunci</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#E3E7EF] dark:border-[#344052] flex items-center justify-between text-xs">
                            <span class="text-[#667085] dark:text-[#A8B2C3]">
                                {{ $room->active_participants_count }} / {{ $room->max_participants }} Peserta
                            </span>
                            <a href="{{ route('rooms.show', $room->room_id) }}" class="font-semibold text-[#315CE8] dark:text-[#7895FF] hover:underline flex items-center gap-1 group">
                                Masuk Room <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function filterRooms() {
        const query = document.getElementById('room-search').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.room-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const id = card.getAttribute('data-id');
            if (name.includes(query) || id.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
