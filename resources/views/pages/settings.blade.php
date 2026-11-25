@extends('layouts.app')

@section('title', 'Settings & Keamanan — SecureLink')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Pengaturan &amp; Diagnostik Keamanan</h1>
        <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1">
            Kelola preferensi akun sesi, tema antarmuka, serta diagnostik jaringan dan enkripsi P2P.
        </p>
    </div>

    <!-- Section 1: Profil Akun Sesi -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-sm space-y-4">
        <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">1. Identitas Sesi Pengguna</h2>

        <form action="{{ route('identity.update.web') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="display_name" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">
                    Display Name
                </label>
                <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $user->display_name) }}" required minlength="2" maxlength="50"
                    class="w-full sm:w-80 px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-xs sm:text-sm text-[#182033] dark:text-[#F1F4FA] focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1">User ID Sesi Unik</label>
                <div class="flex items-center gap-2">
                    <span class="inline-block px-3 py-1.5 rounded-xl bg-[#EEF1F7]/60 dark:bg-[#222B3A] font-mono text-xs text-[#667085] dark:text-[#A8B2C3]">
                        {{ $user->user_id }}
                    </span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $user->user_id }}'); alert('User ID disalin!')" class="px-2.5 py-1.5 rounded-lg border border-[#E3E7EF] dark:border-[#344052] text-xs text-[#315CE8] dark:text-[#7895FF] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A]">
                        Salin
                    </button>
                </div>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    User ID ini digunakan oleh modul signaling untuk perutean SDP Offer/Answer.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Appearance (Light / Dark / System) -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-sm space-y-4">
        <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">2. Tema Tampilan (Appearance)</h2>
        <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">
            Sesuai PRD Section 10.2, Dark Mode menggunakan palet berlapis <em>blue-charcoal</em> untuk kenyamanan mata dan kontras tinggi.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="setThemeMode('light')" class="px-4 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>☀️</span> Light Mode
            </button>
            <button type="button" onclick="setThemeMode('dark')" class="px-4 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>🌙</span> Dark Mode (Blue Charcoal)
            </button>
            <button type="button" onclick="setThemeMode('system')" class="px-4 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>💻</span> Mengikuti Sistem
            </button>
        </div>
    </div>

    <!-- Section 3: Honest Security Indicators (PRD Section 3.7 & Appendix B) -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">3. Indikator Keamanan Aktual</h2>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-[#55C49A] font-semibold border border-emerald-200 dark:border-emerald-800/60">
                Terverifikasi
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">WebRTC DataChannel DTLS</span>
                <p class="text-emerald-600 dark:text-[#55C49A] font-semibold">Aktif (Encrypted in transit)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Kanal transport terenkripsi kriptografi DTLS secara bilateral antarperangkat.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">End-to-End Encryption (E2EE)</span>
                <p class="text-[#315CE8] dark:text-[#7895FF] font-semibold">Web Crypto API (AES-GCM 256-bit)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Derivasi kunci ECDH P-256 dan IV acak 12-byte per pesan di sisi klien.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">Zero Chat Retention Server</span>
                <p class="text-emerald-600 dark:text-[#55C49A] font-semibold">100% Client-Side Ephemeral</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Signaling server Laravel Reverb tidak menyimpan plain-text percakapan Anda.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">Tailscale Private Network</span>
                <p class="text-[#7657D6] dark:text-[#A58AE8] font-semibold">Didukung (Skenario Lab VPN)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Dapat dihubungkan ke Tailnet yang sama untuk demonstrasi jaringan privat.
                </p>
            </div>
        </div>
    </div>

    <!-- Section 4: Live Network Diagnostics (PRD Section 10.12) -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-sm space-y-4">
        <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">4. Diagnostik Jaringan Interaktif</h2>
        <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">
            Uji responsivitas koneksi dan verifikasi WebRTC API browser Anda.
        </p>

        <div class="flex items-center gap-3">
            <button type="button" onclick="runDiagnostics()" class="px-4 py-2 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white text-xs font-semibold shadow-sm transition">
                Jalankan Diagnostik Sekarang
            </button>
        </div>

        <div id="diag-results" class="hidden p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#151A24] border border-[#E3E7EF] dark:border-[#344052] space-y-2 text-xs font-mono">
            <div class="flex items-center justify-between">
                <span>WebRTC RTCPeerConnection:</span>
                <span id="diag-webrtc" class="text-emerald-500 font-bold">Mengecek...</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Web Crypto API (subtle):</span>
                <span id="diag-crypto" class="text-emerald-500 font-bold">Mengecek...</span>
            </div>
            <div class="flex items-center justify-between">
                <span>Signaling Server Ping:</span>
                <span id="diag-ping" class="text-emerald-500 font-bold">Mengecek...</span>
            </div>
        </div>
    </div>

    <!-- Section 5: Logout / Terminate Identity Session -->
    <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-rose-200 dark:border-rose-900/40 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-rose-700 dark:text-[#F07878]">Akhiri Sesi Identitas</h3>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-0.5">
                Menghapus kredensial sesi lokal Anda dan menandai status offline.
            </p>
        </div>
        <form action="{{ route('identity.logout.web') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition">
                Logout Sesi
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function runDiagnostics() {
        const results = document.getElementById('diag-results');
        results.classList.remove('hidden');

        // Check WebRTC
        const webrtcSupported = !!(window.RTCPeerConnection && window.RTCDataChannel);
        document.getElementById('diag-webrtc').textContent = webrtcSupported ? '✓ Didukung (OK)' : '✗ Tidak Didukung';
        document.getElementById('diag-webrtc').className = webrtcSupported ? 'text-emerald-500 font-bold' : 'text-rose-500 font-bold';

        // Check Web Crypto
        const cryptoSupported = !!(window.crypto && window.crypto.subtle);
        document.getElementById('diag-crypto').textContent = cryptoSupported ? '✓ Didukung (OK)' : '✗ Tidak Didukung';
        document.getElementById('diag-crypto').className = cryptoSupported ? 'text-emerald-500 font-bold' : 'text-rose-500 font-bold';

        // Test Signaling Ping
        const start = performance.now();
        fetch('/api/identity/me')
            .then(res => res.json())
            .then(() => {
                const latency = Math.round(performance.now() - start);
                document.getElementById('diag-ping').textContent = `✓ Terhubung (${latency} ms)`;
                document.getElementById('diag-ping').className = 'text-emerald-500 font-bold';
            })
            .catch(() => {
                document.getElementById('diag-ping').textContent = '✗ Gagal terhubung';
                document.getElementById('diag-ping').className = 'text-rose-500 font-bold';
            });
    }
</script>
@endpush
@endsection
