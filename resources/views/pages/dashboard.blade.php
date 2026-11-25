@extends('layouts.app')

@section('title', 'Dashboard — SecureLink')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Card -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl font-bold uppercase shadow-sm" style="background-color: {{ $user->avatar_color }}">
                {{ substr($user->display_name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Halo, {{ $user->display_name }}!</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">ID: {{ $user->user_id }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-medium">Sesi Aktif</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('rooms.create') }}" class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Room Baru
            </a>
            <a href="{{ route('rooms.join') }}" class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-slate-100 dark:bg-[#1f293d] hover:bg-slate-200 dark:hover:bg-[#25334d] text-slate-700 dark:text-slate-200 font-semibold text-xs transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Gabung Room
            </a>
        </div>
    </div>

    <!-- Main Content Area: Recent Rooms -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Room Aktif</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Room yang telah Anda ikuti pada sesi ini</p>
            </div>
            <span class="text-xs font-mono text-slate-400">{{ $participations->count() }} Room</span>
        </div>

        @if($participations->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-slate-200 dark:border-[#1f293d] rounded-xl">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#1f293d] text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada obrolan room</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Anda belum bergabung atau membuat room obrolan. Buat room baru atau minta Room ID dari teman Anda.
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="{{ route('rooms.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition">Buat Room</a>
                    <a href="{{ route('rooms.join') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 dark:border-[#1f293d] text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-[#1f293d] transition">Gabung Room</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($participations as $part)
                    @php $room = $part->room; @endphp
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-[#1f293d] bg-slate-50/50 dark:bg-[#151d30]/50 hover:border-blue-300 dark:hover:border-blue-900 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate" title="{{ $room->room_name }}">
                                    {{ $room->room_name }}
                                </h3>
                                @if($part->isCreator())
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-semibold shrink-0">Creator</span>
                                @else
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium shrink-0">Anggota</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs font-mono text-slate-500 dark:text-slate-400 mb-3">
                                <span>{{ $room->room_id }}</span>
                                @if($room->hasPin())
                                    <span class="text-amber-500" title="Dilindungi PIN">🔒 PIN</span>
                                @endif
                                @if($room->isLocked())
                                    <span class="text-rose-500" title="Room Dikunci">⛔ Terkunci</span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-[#1f293d] flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">
                                {{ $room->active_participants_count }} / {{ $room->max_participants }} Peserta
                            </span>
                            <a href="{{ route('rooms.show', $room->room_id) }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                Masuk Room &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
