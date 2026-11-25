@extends('layouts.app')

@section('title', 'Gabung Room — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12">
    <div class="glass-card rounded-3xl p-6 sm:p-9 shadow-lg relative overflow-hidden">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-blue-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Gabung ke Chat Room</h1>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1.5">
                Masukkan Room ID dan PIN (jika room dilindungi kode akses).
            </p>
        </div>

        <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-4">
            @csrf
            @if(!empty($inviteToken))
                <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
            @endif

            <div>
                <label for="room_id" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    Room ID <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="room_id" name="room_id" required
                    value="{{ old('room_id', $prefilledRoomId) }}"
                    placeholder="Contoh: SEC-8F2-9K1"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#151A24]/80 text-sm font-mono text-[#182033] dark:text-[#F1F4FA] uppercase focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF] transition">
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    Dapatkan Room ID dari pembuat room atau melalui tautan undangan.
                </p>
            </div>

            <div>
                <label for="pin" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    Room PIN (Jika Diwajibkan)
                </label>
                <input type="password" id="pin" name="pin" maxlength="16"
                    placeholder="Masukkan PIN statis atau Dynamic PIN 6-digit"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#151A24]/80 text-sm text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF] transition">
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    Jika room mengaktifkan Dynamic PIN, masukkan kode TOTP 6-digit yang sedang aktif.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-[#315CE8] to-[#254BC5] hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-blue-500/25 transition-all hover:-translate-y-0.5">
                    Gabung ke Room
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-[#E3E7EF] dark:border-[#344052] text-center">
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">
                Ingin membuat room baru?
                <a href="{{ route('rooms.create') }}" class="text-[#315CE8] dark:text-[#7895FF] font-semibold hover:underline">
                    Buat Room Sekarang
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
