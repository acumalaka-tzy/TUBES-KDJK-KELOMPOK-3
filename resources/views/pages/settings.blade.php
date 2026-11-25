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
    <div class="glass-card p-6 sm:p-7 rounded-3xl space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-wider">1. Identitas Sesi Pengguna</h2>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-[#25D366] font-semibold border border-emerald-200 dark:border-emerald-800/60">
                Sesi Aktif
            </span>
        </div>

        <form action="{{ route('identity.update.web') }}" method="POST" class="space-y-5">
            @csrf
            @php
                $settingsColor = old('avatar_color', $user->avatar_color ?? '#008069');
                $settingsIcon = old('avatar_icon', $user->avatar_icon ?? 'initial');
                $displaySymbols = \App\Models\UserSession::$avatarSymbols;
                $settingsDisplay = ($settingsIcon !== 'initial' && isset($displaySymbols[$settingsIcon]))
                    ? $displaySymbols[$settingsIcon]
                    : strtoupper(substr(old('display_name', $user->display_name ?? 'A'), 0, 1) ?: 'U');
            @endphp
            <input type="hidden" id="settings_avatar_icon" name="avatar_icon" value="{{ $settingsIcon }}">

            <!-- Avatar Preview Badge -->
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/50 dark:bg-[#111B21]/50 border border-[#E9EDEF] dark:border-[#222D34]">
                <div id="settings-avatar-preview" class="w-16 h-16 rounded-2xl text-white flex items-center justify-center text-2xl font-extrabold shadow-md shadow-emerald-500/20 shrink-0 uppercase transition-all duration-300" style="background-color: {{ $settingsColor }};">
                    {{ $settingsDisplay }}
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Avatar &amp; Karakter Anda</h3>
                    <p class="text-xs text-[#667781] dark:text-[#8696A0] mt-0.5">
                        Pilih karakter dan warna latar belakang yang merepresentasikan Anda di room.
                    </p>
                </div>
            </div>

            <!-- Avatar Preset Grid -->
            <div>
                <label class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-2">
                    Pilihan Karakter Avatar
                </label>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @php
                        $presets = [
                            'initial' => ['symbol' => '🔤', 'label' => 'Inisial'],
                            'shield' => ['symbol' => '🛡️', 'label' => 'Shield'],
                            'flash' => ['symbol' => '⚡', 'label' => 'Flash'],
                            'bot' => ['symbol' => '🤖', 'label' => 'Robot'],
                            'fox' => ['symbol' => '🦊', 'label' => 'Fox'],
                            'lock' => ['symbol' => '🔐', 'label' => 'Lock'],
                            'rocket' => ['symbol' => '🚀', 'label' => 'Rocket'],
                            'cat' => ['symbol' => '🐱', 'label' => 'Cat'],
                            'owl' => ['symbol' => '🦉', 'label' => 'Owl'],
                            'diamond' => ['symbol' => '💎', 'label' => 'Diamond'],
                            'globe' => ['symbol' => '🌐', 'label' => 'Globe'],
                            'ninja' => ['symbol' => '🕶️', 'label' => 'Agent'],
                        ];
                    @endphp
                    @foreach($presets as $key => $preset)
                        <button type="button"
                            onclick="selectSettingsAvatarPreset('{{ $key }}', '{{ $preset['symbol'] }}')"
                            id="settings-preset-btn-{{ $key }}"
                            class="settings-avatar-btn p-2 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-1 {{ $settingsIcon === $key ? 'border-[#008069] dark:border-[#00A884] bg-emerald-50 dark:bg-emerald-950/40 ring-2 ring-[#008069] dark:ring-[#00A884]' : 'border-[#E9EDEF] dark:border-[#222D34] bg-white/60 dark:bg-[#202C33]/60 hover:border-[#008069]/40' }}">
                            <span class="text-xl select-none">{{ $preset['symbol'] }}</span>
                            <span class="text-[10px] font-medium text-[#667781] dark:text-[#8696A0]">{{ $preset['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- WhatsApp Color Swatches -->
            <div>
                <label class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-2">
                    Warna Latar Avatar (Palet WhatsApp)
                </label>
                <div class="flex items-center gap-2.5 flex-wrap pt-1">
                    @php
                        $colors = ['#008069', '#25D366', '#128C7E', '#00A884', '#075E54', '#34B7F1', '#1F2C34'];
                    @endphp
                    @foreach($colors as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $settingsColor === $c ? 'checked' : '' }} onchange="updateSettingsAvatarColor('{{ $c }}')">
                            <div class="w-8 h-8 rounded-full peer-checked:ring-3 peer-checked:ring-offset-2 peer-checked:ring-[#008069] dark:peer-checked:ring-[#00A884] dark:peer-checked:ring-offset-[#111B21] transition-all duration-200 hover:scale-110 shadow-sm" style="background-color: {{ $c }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="display_name" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">
                    Display Name
                </label>
                <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $user->display_name) }}" required minlength="2" maxlength="50"
                    oninput="updateSettingsAvatarPreview()"
                    class="w-full sm:w-80 px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white dark:bg-[#111B21] text-xs sm:text-sm text-[#111B21] dark:text-[#E9EDEF] focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1">User ID Sesi Unik</label>
                <div class="flex items-center gap-2">
                    <span class="inline-block px-3 py-1.5 rounded-xl bg-[#F0F2F5]/80 dark:bg-[#202C33] font-mono text-xs text-[#667781] dark:text-[#8696A0]">
                        {{ $user->user_id }}
                    </span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $user->user_id }}'); alert('User ID disalin!')" class="px-2.5 py-1.5 rounded-lg border border-[#E9EDEF] dark:border-[#222D34] text-xs text-[#008069] dark:text-[#25D366] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                        Salin
                    </button>
                </div>
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                    User ID ini digunakan oleh modul signaling untuk perutean SDP Offer/Answer.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#008069] hover:bg-[#00A884] text-white text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan Identitas
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Appearance (Light / Dark / System) -->
    <div class="glass-card p-6 sm:p-7 rounded-3xl space-y-4">
        <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">2. Tema Tampilan (Appearance)</h2>
        <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">
            Sesuai PRD Section 10.2, Dark Mode menggunakan palet berlapis <em>blue-charcoal</em> untuk kenyamanan mata dan kontras tinggi.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="setThemeMode('light')" class="px-4 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>☀️</span> Light Mode
            </button>
            <button type="button" onclick="setThemeMode('dark')" class="px-4 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>🌙</span> Dark Mode (Blue Charcoal)
            </button>
            <button type="button" onclick="setThemeMode('system')" class="px-4 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] transition flex items-center gap-2">
                <span>💻</span> Mengikuti Sistem
            </button>
        </div>
    </div>

    <!-- Section 3: Honest Security Indicators (PRD Section 3.7 & Appendix B) -->
    <div class="glass-card p-6 sm:p-7 rounded-3xl space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">3. Indikator Keamanan Aktual</h2>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-[#55C49A] font-semibold border border-emerald-200 dark:border-emerald-800/60">
                Terverifikasi
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">WebRTC DataChannel DTLS</span>
                <p class="text-emerald-600 dark:text-[#55C49A] font-semibold">Aktif (Encrypted in transit)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Kanal transport terenkripsi kriptografi DTLS secara bilateral antarperangkat.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">End-to-End Encryption (E2EE)</span>
                <p class="text-[#315CE8] dark:text-[#7895FF] font-semibold">Web Crypto API (AES-GCM 256-bit)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Derivasi kunci ECDH P-256 dan IV acak 12-byte per pesan di sisi klien.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">Zero Chat Retention Server</span>
                <p class="text-emerald-600 dark:text-[#55C49A] font-semibold">100% Client-Side Ephemeral</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Signaling server Laravel Reverb tidak menyimpan plain-text percakapan Anda.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-[#EEF1F7]/50 dark:bg-[#222B3A]/60 border border-[#E3E7EF] dark:border-[#344052] space-y-1">
                <span class="font-bold text-[#182033] dark:text-[#F1F4FA]">Tailscale Private Network</span>
                <p class="text-[#7657D6] dark:text-[#A58AE8] font-semibold">Didukung (Skenario Lab VPN)</p>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">
                    Dapat dihubungkan ke Tailnet yang sama untuk demonstrasi jaringan privat.
                </p>
            </div>
        </div>
    </div>

    <!-- Section 4: Live Network Diagnostics (PRD Section 10.12) -->
    <div class="glass-card p-6 sm:p-7 rounded-3xl space-y-4">
        <h2 class="text-xs font-bold text-[#315CE8] dark:text-[#7895FF] uppercase tracking-wider">4. Diagnostik Jaringan Interaktif</h2>
        <p class="text-xs text-[#667085] dark:text-[#A8B2C3]">
            Uji responsivitas koneksi dan verifikasi WebRTC API browser Anda.
        </p>

        <div class="flex items-center gap-3">
            <button type="button" onclick="runDiagnostics()" class="px-4 py-2 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white text-xs font-semibold shadow-sm transition">
                Jalankan Diagnostik Sekarang
            </button>
            <button type="button" onclick="exportDiagnosticLogs()" class="px-4 py-2 rounded-xl border border-[#E3E7EF] dark:border-[#344052] hover:bg-[#EEF1F7] dark:hover:bg-[#222B3A] text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Log Diagnostik (JSON)
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

    function exportDiagnosticLogs() {
        const diagnosticsData = {
            appName: 'SecureLink',
            version: '2.1',
            timestamp: new Date().toISOString(),
            user: {
                userId: '{{ $user->user_id }}',
                displayName: '{{ $user->display_name }}'
            },
            clientEnvironment: {
                userAgent: navigator.userAgent,
                language: navigator.language,
                screenResolution: `${window.innerWidth}x${window.innerHeight}`,
                themePreference: localStorage.getItem('securelink_theme') || 'system',
                webRtcSupported: !!(window.RTCPeerConnection && window.RTCDataChannel),
                webCryptoSupported: !!(window.crypto && window.crypto.subtle)
            },
            securityCompliance: {
                dtlsTransport: 'Encrypted in transit',
                e2eeCipher: 'AES-GCM (256-bit)',
                keyDerivation: 'ECDH P-256',
                serverChatRetention: 'None (Zero Knowledge)'
            }
        };

        const blob = new Blob([JSON.stringify(diagnosticsData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `securelink-diagnostics-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Avatar preview controls for Settings
    let currentSettingsIcon = "{{ $settingsIcon }}";
    let currentSettingsSymbol = "{{ $settingsIcon === 'initial' ? '' : ($displaySymbols[$settingsIcon] ?? '') }}";

    function selectSettingsAvatarPreset(key, symbol) {
        currentSettingsIcon = key;
        currentSettingsSymbol = (key === 'initial') ? '' : symbol;
        document.getElementById('settings_avatar_icon').value = key;

        document.querySelectorAll('.settings-avatar-btn').forEach(btn => {
            btn.classList.remove('border-[#008069]', 'dark:border-[#00A884]', 'bg-emerald-50', 'dark:bg-emerald-950/40', 'ring-2', 'ring-[#008069]', 'dark:ring-[#00A884]');
            btn.classList.add('border-[#E9EDEF]', 'dark:border-[#222D34]', 'bg-white/60', 'dark:bg-[#202C33]/60');
        });

        const selectedBtn = document.getElementById('settings-preset-btn-' + key);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-[#E9EDEF]', 'dark:border-[#222D34]', 'bg-white/60', 'dark:bg-[#202C33]/60');
            selectedBtn.classList.add('border-[#008069]', 'dark:border-[#00A884]', 'bg-emerald-50', 'dark:bg-emerald-950/40', 'ring-2', 'ring-[#008069]', 'dark:ring-[#00A884]');
        }

        updateSettingsAvatarPreview();
    }

    function updateSettingsAvatarPreview() {
        const preview = document.getElementById('settings-avatar-preview');
        if (currentSettingsIcon !== 'initial' && currentSettingsSymbol) {
            preview.textContent = currentSettingsSymbol;
        } else {
            const name = document.getElementById('display_name').value.trim();
            preview.textContent = name ? name.charAt(0).toUpperCase() : 'U';
        }
    }

    function updateSettingsAvatarColor(color) {
        const preview = document.getElementById('settings-avatar-preview');
        preview.style.backgroundColor = color;
    }
</script>
@endpush
@endsection
