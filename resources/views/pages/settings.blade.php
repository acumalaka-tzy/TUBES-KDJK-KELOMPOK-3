@extends('layouts.app')

@section('title', 'Settings & Keamanan — SecureLink')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Pengaturan &amp; Diagnostik</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Kelola preferensi identitas sesi, tema tampilan, dan informasi keamanan jaringan.
        </p>
    </div>

    <!-- Section 1: Profil Akun Sesi -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Identitas Sesi</h2>

        <form action="{{ route('identity.update.web') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="display_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Display Name</label>
                <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $user->display_name) }}"
                    class="w-full sm:w-80 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">User ID Unik</label>
                <span class="inline-block px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-[#151d30] font-mono text-xs text-slate-600 dark:text-slate-400">
                    {{ $user->user_id }}
                </span>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Appearance -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Tema Tampilan (Appearance)</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Sesuai PRD, Dark Mode menggunakan palet warna blue-charcoal untuk kenyamanan mata.
        </p>
        <div class="flex items-center gap-3">
            <button type="button" onclick="setThemeMode('light')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-[#1f293d] text-xs font-semibold hover:bg-slate-50 dark:hover:bg-[#1f293d]">
                ☀️ Light Mode
            </button>
            <button type="button" onclick="setThemeMode('dark')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-[#1f293d] text-xs font-semibold hover:bg-slate-50 dark:hover:bg-[#1f293d]">
                🌙 Dark Mode (Blue Charcoal)
            </button>
            <button type="button" onclick="setThemeMode('system')" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-[#1f293d] text-xs font-semibold hover:bg-slate-50 dark:hover:bg-[#1f293d]">
                💻 Sistem Otomatis
            </button>
        </div>
    </div>

    <!-- Section 3: Honest Security Indicators (PRD Section 3.7 & Appendix B) -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Status Keamanan &amp; Jaringan</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] space-y-1">
                <span class="font-bold text-slate-700 dark:text-slate-300">WebRTC DataChannel DTLS</span>
                <p class="text-emerald-600 dark:text-emerald-400 font-semibold">Aktif (Encrypted in transit)</p>
                <p class="text-[11px] text-slate-500">Mengamankan kanal transport biner antarperangkat.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] space-y-1">
                <span class="font-bold text-slate-700 dark:text-slate-300">End-to-End Encryption (E2EE)</span>
                <p class="text-blue-600 dark:text-blue-400 font-semibold">AES-GCM (Browser Web Crypto API)</p>
                <p class="text-[11px] text-slate-500">Isi pesan dienkripsi langsung di browser pengirim.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] space-y-1">
                <span class="font-bold text-slate-700 dark:text-slate-300">Tailscale Private Network</span>
                <p class="text-indigo-600 dark:text-indigo-400 font-semibold">Didukung (Konfigurasi Pengujian)</p>
                <p class="text-[11px] text-slate-500">Memberikan lapisan jaringan virtual privat antar-node.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] space-y-1">
                <span class="font-bold text-slate-700 dark:text-slate-300">Signaling Server Storage</span>
                <p class="text-emerald-600 dark:text-emerald-400 font-semibold">Zero Chat Retention</p>
                <p class="text-[11px] text-slate-500">Server tidak menyimpan plain-text pesan obrolan.</p>
            </div>
        </div>
    </div>

    <!-- Section 4: Sesi Selesai -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-rose-200 dark:border-rose-900/40 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">Akhiri Sesi Identitas</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Menghapus sesi lokal dan menandai status sebagai offline.</p>
        </div>
        <form action="{{ route('identity.logout.web') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition">
                Logout
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function setThemeMode(mode) {
        localStorage.setItem('theme', mode);
        applyTheme(mode);
    }
</script>
@endpush
@endsection
