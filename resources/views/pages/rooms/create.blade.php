@extends('layouts.app')

@section('title', 'Buat Room Baru — SecureLink')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <div class="glass-card rounded-3xl p-6 sm:p-9 shadow-lg relative overflow-hidden">
        <div class="mb-6 pb-4 border-b border-[#E9EDEF] dark:border-[#222D34]">
            <h1 class="text-xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Buat Chat Room Baru</h1>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] mt-1.5">
                Atur konfigurasi keamanan, enkripsi end-to-end, dan pembatasan room P2P Anda.
            </p>
        </div>

        <form action="{{ route('rooms.store.web') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Info -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-wider">1. Informasi Dasar Room</h3>

                <div>
                    <label for="room_name" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">
                        Nama Room <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="room_name" name="room_name" required maxlength="100"
                        value="{{ old('room_name') }}"
                        placeholder="Contoh: Lab Jaringan Komputer Kelompok 3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF] focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884]">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="room_id" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF]">
                            Room ID (Kustom atau Otomatis)
                        </label>
                        <button type="button" onclick="generateNewRoomId()" class="text-[11px] text-[#008069] dark:text-[#25D366] font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Acak Ulang
                        </button>
                    </div>
                    <input type="text" id="room_id" name="room_id"
                        value="{{ old('room_id', $suggestedRoomId) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm font-mono text-[#111B21] dark:text-[#E9EDEF] uppercase focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884]">
                </div>

                <div>
                    <label for="max_participants" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">
                        Batas Maksimum Peserta (2 - 50)
                    </label>
                    <input type="number" id="max_participants" name="max_participants" min="2" max="50"
                        value="{{ old('max_participants', 10) }}"
                        class="w-32 px-3.5 py-2 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF]">
                </div>
            </div>

            <!-- Section 2: Security & PIN -->
            <div class="space-y-4 pt-4 border-t border-[#E9EDEF] dark:border-[#222D34]">
                <h3 class="text-xs font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-wider">2. Keamanan &amp; Autentikasi Room</h3>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">
                        Room PIN Statis (Opsional)
                    </label>
                    <input type="password" id="pin" name="pin" maxlength="16" placeholder="Kosongkan jika room terbuka tanpa PIN"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF] focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884]">
                    <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                        PIN dienkripsi secara aman dengan algoritma bcrypt dan tidak pernah disimpan dalam bentuk plain-text.
                    </p>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#F0F2F5]/60 dark:bg-[#202C33]/60 border border-[#E9EDEF] dark:border-[#222D34]">
                    <input type="checkbox" id="dynamic_pin_enabled" name="dynamic_pin_enabled" value="1" {{ old('dynamic_pin_enabled') ? 'checked' : '' }}
                        class="mt-0.5 rounded text-[#008069] focus:ring-[#008069] h-4 w-4">
                    <label for="dynamic_pin_enabled" class="text-xs">
                        <span class="font-bold text-[#111B21] dark:text-[#E9EDEF] block">Dynamic PIN (TOTP 30s)</span>
                        <span class="text-[#667781] dark:text-[#8696A0] block mt-0.5">
                            Menghasilkan kode PIN dinamis 6-digit berbasis waktu yang berganti setiap 30 detik untuk perlindungan akses real-time.
                        </span>
                    </label>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#F0F2F5]/60 dark:bg-[#202C33]/60 border border-[#E9EDEF] dark:border-[#222D34]">
                    <input type="checkbox" id="e2ee_enabled" name="e2ee_enabled" value="1" checked
                        class="mt-0.5 rounded text-[#008069] focus:ring-[#008069] h-4 w-4">
                    <label for="e2ee_enabled" class="text-xs">
                        <span class="font-bold text-[#111B21] dark:text-[#E9EDEF] block">End-to-End Encryption (E2EE)</span>
                        <span class="text-[#667781] dark:text-[#8696A0] block mt-0.5">
                            Pesan dienkripsi pada perangkat pengirim menggunakan Web Crypto API (AES-GCM) sebelum dilewatkan ke WebRTC DataChannel.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Section 3: Ephemeral Messages & Permissions -->
            <div class="space-y-4 pt-4 border-t border-[#E9EDEF] dark:border-[#222D34]">
                <h3 class="text-xs font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-wider">3. Pengaturan Pesan Sementara</h3>

                <div>
                    <label for="default_ephemeral_duration" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">
                        Durasi Pesan Ephemeral Standar
                    </label>
                    <select id="default_ephemeral_duration" name="default_ephemeral_duration"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF]">
                        <option value="0">Nonaktif (Pesan Normal)</option>
                        <option value="10">10 Detik</option>
                        <option value="30">30 Detik</option>
                        <option value="60">60 Detik (1 Menit)</option>
                        <option value="300">300 Detik (5 Menit)</option>
                    </select>
                    <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                        Pesan sementara akan memiliki countdown timer dan otomatis dihapus dari memori aplikasi setelah kedaluwarsa.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-2xl glass-card text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] transition-all hover:-translate-y-0.5">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#008069] to-[#00A884] hover:opacity-95 text-white text-xs font-semibold shadow-md shadow-emerald-600/25 transition-all hover:-translate-y-0.5">
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
