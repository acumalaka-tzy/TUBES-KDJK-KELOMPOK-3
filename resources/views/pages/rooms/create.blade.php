@extends('layouts.app')

@section('title', 'Buat Room Baru — SecureLink')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <div class="glass-card rounded-3xl p-6 sm:p-9 shadow-lg relative overflow-hidden">
        <div class="mb-6 pb-4 border-b border-[#E3E7EF] dark:border-[#344052]">
            <h1 class="text-xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Buat Chat Room Baru</h1>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1.5">
                Atur konfigurasi keamanan, enkripsi end-to-end, dan pembatasan room P2P Anda.
            </p>
        </div>

        <form action="{{ route('rooms.store.web') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Info -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">1. Informasi Dasar Room</h3>

                <div>
                    <label for="room_name" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">
                        Nama Room <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="room_name" name="room_name" required maxlength="100"
                        value="{{ old('room_name') }}"
                        placeholder="Contoh: Lab Jaringan Komputer Kelompok 3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="room_id" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA]">
                            Room ID (Kustom atau Otomatis)
                        </label>
                        <button type="button" onclick="generateNewRoomId()" class="text-[11px] text-[#315CE8] dark:text-[#7895FF] font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Acak Ulang
                        </button>
                    </div>
                    <input type="text" id="room_id" name="room_id"
                        value="{{ old('room_id', $suggestedRoomId) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm font-mono text-[#182033] dark:text-[#F1F4FA] uppercase focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
                </div>

                <div>
                    <label for="max_participants" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">
                        Batas Maksimum Peserta (2 - 50)
                    </label>
                    <input type="number" id="max_participants" name="max_participants" min="2" max="50"
                        value="{{ old('max_participants', 10) }}"
                        class="w-32 px-3.5 py-2 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA]">
                </div>
            </div>

            <!-- Section 2: Security & PIN -->
            <div class="space-y-4 pt-4 border-t border-[#E3E7EF] dark:border-[#344052]">
                <h3 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">2. Keamanan &amp; Autentikasi Room</h3>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">
                        Room PIN Statis (Opsional)
                    </label>
                    <input type="password" id="pin" name="pin" maxlength="16" placeholder="Kosongkan jika room terbuka tanpa PIN"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
                    <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                        PIN dienkripsi secara aman dengan algoritma bcrypt dan tidak pernah disimpan dalam bentuk plain-text.
                    </p>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052]">
                    <input type="checkbox" id="dynamic_pin_enabled" name="dynamic_pin_enabled" value="1" {{ old('dynamic_pin_enabled') ? 'checked' : '' }}
                        class="mt-0.5 rounded text-[#315CE8] focus:ring-[#315CE8] h-4 w-4">
                    <label for="dynamic_pin_enabled" class="text-xs">
                        <span class="font-bold text-[#182033] dark:text-[#F1F4FA] block">Dynamic PIN (TOTP 30s)</span>
                        <span class="text-[#667085] dark:text-[#A8B2C3] block mt-0.5">
                            Menghasilkan kode PIN dinamis 6-digit berbasis waktu yang berganti setiap 30 detik untuk perlindungan akses real-time.
                        </span>
                    </label>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052]">
                    <input type="checkbox" id="e2ee_enabled" name="e2ee_enabled" value="1" checked
                        class="mt-0.5 rounded text-[#315CE8] focus:ring-[#315CE8] h-4 w-4">
                    <label for="e2ee_enabled" class="text-xs">
                        <span class="font-bold text-[#182033] dark:text-[#F1F4FA] block">End-to-End Encryption (E2EE)</span>
                        <span class="text-[#667085] dark:text-[#A8B2C3] block mt-0.5">
                            Pesan dienkripsi pada perangkat pengirim menggunakan Web Crypto API (AES-GCM) sebelum dilewatkan ke WebRTC DataChannel.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Section 3: Ephemeral Messages & Permissions -->
            <div class="space-y-4 pt-4 border-t border-[#E3E7EF] dark:border-[#344052]">
                <h3 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">3. Pengaturan Pesan Sementara</h3>

                <div>
                    <label for="default_ephemeral_duration" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">
                        Durasi Pesan Ephemeral Standar
                    </label>
                    <select id="default_ephemeral_duration" name="default_ephemeral_duration"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA]">
                        <option value="0">Nonaktif (Pesan Normal)</option>
                        <option value="10">10 Detik</option>
                        <option value="30">30 Detik</option>
                        <option value="60">60 Detik (1 Menit)</option>
                        <option value="300">300 Detik (5 Menit)</option>
                    </select>
                    <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                        Pesan sementara akan memiliki countdown timer dan otomatis dihapus dari memori aplikasi setelah kedaluwarsa.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-2xl glass-card text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] transition-all hover:-translate-y-0.5">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#315CE8] to-[#254BC5] hover:opacity-95 text-white text-xs font-semibold shadow-md shadow-blue-500/25 transition-all hover:-translate-y-0.5">
                    Buat Room Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function generateNewRoomId() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let part1 = '';
        let part2 = '';
        for (let i = 0; i < 3; i++) part1 += chars.charAt(Math.floor(Math.random() * chars.length));
        for (let i = 0; i < 3; i++) part2 += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('room_id').value = `SEC-${part1}-${part2}`;
    }
</script>
@endpush
@endsection
