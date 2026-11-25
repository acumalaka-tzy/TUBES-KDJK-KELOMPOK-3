@extends('layouts.app')

@section('title', 'Gabung Room — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12">
    <div class="glass-card rounded-3xl p-6 sm:p-9 shadow-lg relative overflow-hidden">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#008069] to-[#25D366] text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Gabung ke Chat Room</h1>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] mt-1.5">
                Masukkan Room ID dan PIN (jika room dilindungi kode akses).
            </p>
        </div>

        <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-4">
            @csrf
            @if(!empty($inviteToken))
                <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
            @endif

            <div>
                <label for="room_id" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1.5">
                    Room ID <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="room_id" name="room_id" required
                    value="{{ old('room_id', $prefilledRoomId) }}"
                    placeholder="Contoh: SEC-8F2-9K1"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm font-mono text-[#111B21] dark:text-[#E9EDEF] uppercase focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884] transition">
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                    Dapatkan Room ID dari pembuat room atau melalui tautan undangan.
                </p>
            </div>

            <div>
                <label for="pin" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1.5">
                    Room PIN (Jika Diwajibkan)
                </label>
                <input type="password" id="pin" name="pin" maxlength="16"
                    placeholder="Masukkan PIN statis atau Dynamic PIN 6-digit"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF] focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884] transition">
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                    Jika room mengaktifkan Dynamic PIN, masukkan kode TOTP 6-digit yang sedang aktif.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-[#008069] to-[#00A884] hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-emerald-600/25 transition-all hover:-translate-y-0.5">
                    Gabung ke Room
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-[#E9EDEF] dark:border-[#222D34] text-center">
            <p class="text-xs text-[#667781] dark:text-[#8696A0]">
                Ingin membuat room baru?
                <a href="{{ route('rooms.create') }}" class="text-[#008069] dark:text-[#25D366] font-semibold hover:underline">
                    Buat Room Sekarang
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
