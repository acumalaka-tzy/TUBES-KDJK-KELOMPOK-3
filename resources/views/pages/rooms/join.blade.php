@extends('layouts.app')

@section('title', 'Gabung Room — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-10">
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Gabung ke Room</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Masukkan Room ID dan PIN (jika diwajibkan oleh Room Creator)
            </p>
        </div>

        <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-4">
            @csrf
            @if(!empty($inviteToken))
                <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
            @endif

            <div>
                <label for="room_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Room ID *</label>
                <input type="text" id="room_id" name="room_id" required
                    value="{{ old('room_id', $prefilledRoomId) }}"
                    placeholder="Contoh: SEC-8F2-9K1"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm font-mono text-slate-900 dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="pin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Room PIN</label>
                <input type="password" id="pin" name="pin"
                    placeholder="Masukkan PIN jika room dilindungi"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    Bisa berupa PIN statis atau Dynamic PIN 6-digit (TOTP).
                </p>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                    Bergabung ke Room
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-[#1f293d] text-center">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Ingin membuat ruang baru?
                <a href="{{ route('rooms.create') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Buat Room</a>
            </p>
        </div>
    </div>
</div>
@endsection
