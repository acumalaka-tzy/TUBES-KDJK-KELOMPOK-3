@extends('layouts.app')

@section('title', 'Buat Room Baru — SecureLink')

@section('content')
@php
    $chatType = request()->query('type', 'group');
    $defaultMax = $chatType === 'private' ? 2 : 10;
@endphp
<div class="max-w-xl mx-auto py-6">
    <div class="sl-surface rounded-xl p-6 sm:p-8 space-y-6">
        <div class="pb-4 border-b border-[var(--sl-divider)]">
            <h1 class="text-xl font-bold text-[var(--sl-text-primary)]">Buat Chat Room Baru</h1>
            <p class="text-xs text-[var(--sl-text-secondary)] mt-1">
                Pilih mode Private Chat (1-on-1) atau Group Chat dengan enkripsi WebRTC E2EE.
            </p>
        </div>

        <form action="{{ route('rooms.store.web') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Quick Type Selector -->
            <div class="grid grid-cols-2 gap-2.5">
                <button type="button" onclick="setRoomPreset('private')" id="preset-private"
                    class="p-3 rounded-lg border text-left transition {{ $chatType === 'private' ? 'border-[#497173] bg-[var(--sl-soft-teal)]' : 'border-[var(--sl-divider)] bg-[var(--sl-main-surface)]' }}">
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Private Chat</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">1 lawan 1 (maks 2 peer)</div>
                </button>
                <button type="button" onclick="setRoomPreset('group')" id="preset-group"
                    class="p-3 rounded-lg border text-left transition {{ $chatType !== 'private' ? 'border-[#497173] bg-[var(--sl-soft-teal)]' : 'border-[var(--sl-divider)] bg-[var(--sl-main-surface)]' }}">
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Group Chat</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Mesh P2P multi-anggota</div>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="room_name" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Nama Room <span class="text-[#EC643E]">*</span>
                    </label>
                    <input type="text" id="room_name" name="room_name" required maxlength="100"
                        value="{{ old('room_name') }}"
                        placeholder="Contoh: Diskusi Proyek Jaringan"
                        class="w-full px-3.5 py-2.5 rounded-lg sl-input text-base sm:text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="room_id" class="block text-xs font-semibold text-[var(--sl-text-primary)]">
                            Room ID
                        </label>
                        <button type="button" onclick="generateNewRoomId()" class="text-[11px] text-[var(--sl-primary-teal)] font-semibold hover:underline">
                            Acak Ulang
                        </button>
                    </div>
                    <input type="text" id="room_id" name="room_id"
                        value="{{ old('room_id', $suggestedRoomId) }}"
                        class="w-full px-3.5 py-2.5 rounded-lg sl-input text-base sm:text-sm font-mono uppercase">
                </div>

                <div>
                    <label for="max_participants" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Batas Maksimum Peserta (2 - 50)
                    </label>
                    <input type="number" id="max_participants" name="max_participants" min="2" max="50"
                        value="{{ old('max_participants', $defaultMax) }}"
                        class="w-full sm:w-32 px-3.5 py-2 rounded-lg sl-input text-base sm:text-sm">
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-[var(--sl-divider)]">
                <h2 class="text-xs font-bold text-[var(--sl-primary-teal)] uppercase tracking-wider">Keamanan Room</h2>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Room PIN Statis (Opsional)
                    </label>
                    <input type="password" id="pin" name="pin" maxlength="16" placeholder="Kosongkan jika tanpa PIN"
                        class="w-full px-3.5 py-2.5 rounded-lg sl-input text-base sm:text-sm">
                </div>

                <label for="dynamic_pin_enabled" class="flex items-start gap-3 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] cursor-pointer">
                    <input type="checkbox" id="dynamic_pin_enabled" name="dynamic_pin_enabled" value="1" {{ old('dynamic_pin_enabled') ? 'checked' : '' }}
                        class="mt-0.5 rounded text-[#497173] h-4 w-4">
                    <div class="text-xs">
                        <span class="font-bold text-[var(--sl-text-primary)] block">Dynamic PIN (TOTP 30 detik)</span>
                        <span class="text-[var(--sl-text-secondary)] block mt-0.5">Menghasilkan kode PIN dinamis 6-digit setiap 30 detik.</span>
                    </div>
                </label>

                <label for="e2ee_enabled" class="flex items-start gap-3 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] cursor-pointer">
                    <input type="checkbox" id="e2ee_enabled" name="e2ee_enabled" value="1" checked
                        class="mt-0.5 rounded text-[#497173] h-4 w-4">
                    <div class="text-xs">
                        <span class="font-bold text-[var(--sl-text-primary)] block">End-to-End Encryption (E2EE)</span>
                        <span class="text-[var(--sl-text-secondary)] block mt-0.5">Enkripsi AES-GCM 256-bit via Web Crypto API sebelum dikirim melalui DataChannel.</span>
                    </div>
                </label>

                <div>
                    <label for="default_ephemeral_duration" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Durasi Pesan Ephemeral Standar
                    </label>
                    <select id="default_ephemeral_duration" name="default_ephemeral_duration" class="w-full px-3.5 py-2.5 rounded-lg sl-input text-base sm:text-sm">
                        <option value="0">Nonaktif (Pesan Normal)</option>
                        <option value="10">10 Detik</option>
                        <option value="30">30 Detik</option>
                        <option value="60">60 Detik (1 Menit)</option>
                        <option value="300">300 Detik (5 Menit)</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-lg sl-btn-secondary text-xs font-semibold text-center min-h-[42px] sm:min-h-0 inline-flex items-center justify-center">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-lg sl-btn-coral text-xs font-semibold min-h-[42px] sm:min-h-0">Buat Room Sekarang</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function setRoomPreset(type) {
        const maxEl = document.getElementById('max_participants');
        const privBtn = document.getElementById('preset-private');
        const grpBtn = document.getElementById('preset-group');
        if (type === 'private') {
            maxEl.value = 2;
            privBtn.classList.add('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
            privBtn.classList.remove('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
            grpBtn.classList.remove('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
            grpBtn.classList.add('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
        } else {
            if (parseInt(maxEl.value) <= 2) maxEl.value = 10;
            grpBtn.classList.add('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
            grpBtn.classList.remove('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
            privBtn.classList.remove('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
            privBtn.classList.add('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
        }
    }

    function generateNewRoomId() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let p1 = '', p2 = '';
        for (let i = 0; i < 3; i++) p1 += chars.charAt(Math.floor(Math.random() * chars.length));
        for (let i = 0; i < 3; i++) p2 += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('room_id').value = `SEC-${p1}-${p2}`;
    }
</script>
@endpush
@endsection
