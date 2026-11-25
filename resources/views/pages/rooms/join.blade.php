@extends('layouts.app')

@section('title', 'Gabung Room — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-10">
    <div class="bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-[#315CE8] dark:text-[#7895FF] flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Gabung ke Chat Room</h1>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1">
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
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm font-mono text-[#182033] dark:text-[#F1F4FA] uppercase focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
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
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    Jika room mengaktifkan Dynamic PIN, masukkan kode TOTP 6-digit yang sedang aktif.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white font-semibold text-sm shadow-sm transition">
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
