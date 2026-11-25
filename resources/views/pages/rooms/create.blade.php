@extends('layouts.app')

@section('title', 'Buat Room Baru — SecureLink')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="mb-6 pb-4 border-b border-slate-100 dark:border-[#1f293d]">
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Buat Chat Room Baru</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Atur konfigurasi keamanan, enkripsi end-to-end, dan pembatasan room P2P Anda.
            </p>
        </div>

        <form action="{{ route('rooms.store.web') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Info -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">1. Informasi Dasar Room</h3>

                <div>
                    <label for="room_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Room *</label>
                    <input type="text" id="room_name" name="room_name" required maxlength="100"
                        value="{{ old('room_name') }}"
                        placeholder="Contoh: Lab Jaringan Komputer Kelompok 3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="room_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Room ID (Kustom atau Otomatis)</label>
                        <button type="button" onclick="generateNewId()" class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Acak Ulang
                        </button>
                    </div>
                    <input type="text" id="room_id" name="room_id"
                        value="{{ old('room_id', $suggestedRoomId) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="max_participants" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Batas Maksimum Peserta (2 - 50)</label>
                    <input type="number" id="max_participants" name="max_participants" min="2" max="50"
                        value="{{ old('max_participants', 10) }}"
                        class="w-32 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white">
                </div>
            </div>

            <!-- Section 2: Security & PIN -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-[#1f293d]">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">2. Keamanan &amp; Autentikasi Room</h3>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Room PIN (Opsional)</label>
                    <input type="password" id="pin" name="pin" maxlength="16" placeholder="Kosongkan jika room terbuka tanpa PIN"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                        PIN dienkripsi secara aman dengan algoritma hashing satu arah dan tidak disimpan dalam plain-text.
                    </p>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d]">
                    <input type="checkbox" id="dynamic_pin_enabled" name="dynamic_pin_enabled" value="1" {{ old('dynamic_pin_enabled') ? 'checked' : '' }}
                        class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                    <label for="dynamic_pin_enabled" class="text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">Dynamic PIN (TOTP)</span>
                        <span class="text-slate-500 dark:text-slate-400 block mt-0.5">
                            Menghasilkan kode PIN dinamis 6-digit berbasis waktu yang berganti setiap 30 detik untuk perlindungan ekstra.
                        </span>
                    </label>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d]">
                    <input type="checkbox" id="e2ee_enabled" name="e2ee_enabled" value="1" checked
                        class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                    <label for="e2ee_enabled" class="text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block">End-to-End Encryption (E2EE)</span>
                        <span class="text-slate-500 dark:text-slate-400 block mt-0.5">
                            Pesan dienkripsi pada perangkat pengguna menggunakan AES-GCM sebelum dikirim ke DataChannel.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Section 3: Ephemeral Messages & Permissions -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-[#1f293d]">
                <h3 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">3. Pengaturan Pesan Sementara &amp; Izin</h3>

                <div>
                    <label for="default_ephemeral_duration" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Durasi Pesan Ephemeral Standar</label>
                    <select id="default_ephemeral_duration" name="default_ephemeral_duration"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white">
                        <option value="0">Nonaktif (Normal)</option>
                        <option value="10">10 Detik</option>
                        <option value="30">30 Detik</option>
                        <option value="60">60 Detik (1 Menit)</option>
                        <option value="300">300 Detik (5 Menit)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="allow_invites" value="1" checked class="rounded text-blue-600">
                        <span>Izinkan Undangan</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="allow_reactions" value="1" checked class="rounded text-blue-600">
                        <span>Emoji Reaction</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="allow_replies" value="1" checked class="rounded text-blue-600">
                        <span>Balas Pesan (Reply)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-[#1f293d]">
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-[#1f293d] transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition">
                    Buat Room Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function generateNewId() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let part1 = '';
        let part2 = '';
        for (let i = 0; i < 3; i++) part1 += chars.charAt(Math.floor(Math.random() * chars.length));
        for (let i = 0; i < 3; i++) part2 += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('room_id').value = 'SEC-' + part1 + '-' + part2;
    }
</script>
@endpush
@endsection
