@extends('layouts.app')

@section('title', 'Pengaturan & Diagnostik — SecureLink')

@section('app_shell')
@php
    $activeSection = request()->query('section', 'account');
    $settingsColor = old('avatar_color', $user->avatar_color ?? '#497173');
    $settingsIcon = old('avatar_icon', $user->avatar_icon ?? 'initial');
    $characterMetadata = \App\Models\UserSession::$characterMetadata;
    $characterSvgs = \App\Models\UserSession::$avatarSvgs;
    $resolvedKey = \App\Models\UserSession::$legacyAliases[$settingsIcon] ?? $settingsIcon;
    $hasSvg = ($resolvedKey !== 'initial' && isset($characterSvgs[$resolvedKey]));
    $initialLetter = strtoupper(substr(old('display_name', $user->display_name ?? 'A'), 0, 1) ?: 'U');
@endphp

<div class="h-screen w-screen overflow-hidden flex bg-[var(--sl-page-bg)] text-[var(--sl-text-primary)]">

    <!-- A. Slim Icon Navigation Sidebar -->
    @include('partials.app-nav')

    <!-- B. Settings Category Sidebar (Column 1) -->
    <aside class="w-full md:w-72 border-r border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] flex flex-col shrink-0 h-full overflow-y-auto sl-scroll">
        <div class="px-4 py-3.5 border-b border-[var(--sl-divider)]">
            <h1 class="text-sm font-bold text-[var(--sl-text-primary)]">Pengaturan &amp; Diagnostik</h1>
            <p class="text-[11px] text-[var(--sl-text-secondary)] mt-0.5">Akun, tampilan, keamanan &amp; koneksi</p>
        </div>

        <nav class="p-2 space-y-0.5 text-xs font-medium">
            @php
                $categories = [
                    'account' => ['label' => 'Account', 'desc' => 'Profil & identitas sesi'],
                    'appearance' => ['label' => 'Appearance', 'desc' => 'Tema terang, gelap & font'],
                    'security' => ['label' => 'Privacy & Security', 'desc' => 'E2EE, DTLS & ephemeral'],
                    'notifications' => ['label' => 'Notifications', 'desc' => 'Suara & notifikasi desktop'],
                    'connection' => ['label' => 'Connection', 'desc' => 'WebRTC, STUN, TURN & signaling'],
                    'chat' => ['label' => 'Chat Preferences', 'desc' => 'Perilaku composer & pesan'],
                    'about' => ['label' => 'About & Help', 'desc' => 'Versi, dokumentasi & keluar'],
                ];
            @endphp
            @foreach($categories as $key => $cat)
                <button type="button" onclick="showSettingsSection('{{ $key }}')" data-sec-btn="{{ $key }}"
                    class="sec-nav-btn w-full text-left px-3 py-2.5 rounded-lg transition flex flex-col {{ $activeSection === $key ? 'bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)]' }}">
                    <span class="font-semibold">{{ $cat['label'] }}</span>
                    <span class="text-[10px] text-[var(--sl-text-secondary)]">{{ $cat['desc'] }}</span>
                </button>
            @endforeach
        </nav>
    </aside>

    <!-- C. Settings Detail Panel (Column 2) -->
    <main class="flex-1 bg-[var(--sl-chat-bg)] overflow-y-auto sl-scroll p-5 sm:p-8 h-full">
        <div class="max-w-2xl space-y-6">

            @if(session('success'))
                <div class="px-4 py-2.5 rounded-lg bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 4.1 Account -->
            <section id="sec-account" class="sec-panel space-y-5 {{ $activeSection === 'account' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Account</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Kelola tampilan profil dan informasi akun sesi Anda.</p>
                </div>

                <form action="{{ route('identity.update.web') }}" method="POST" class="sl-surface rounded-xl p-5 space-y-5">
                    @csrf
                    <input type="hidden" id="settings_avatar_icon" name="avatar_icon" value="{{ $settingsIcon }}">

                    <div class="flex items-center gap-4">
                        <div id="settings-avatar-preview" class="w-14 h-14 rounded-full text-white flex items-center justify-center text-xl font-bold uppercase p-2 overflow-hidden shrink-0" style="background-color: {{ $settingsColor }};">
                            @if($hasSvg)
                                <div class="w-full h-full flex items-center justify-center">{!! $characterSvgs[$resolvedKey] !!}</div>
                            @else
                                <span>{{ $initialLetter }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-[var(--sl-text-primary)]">{{ $user->display_name }}</div>
                            <div class="text-[11px] font-mono text-[var(--sl-text-secondary)]">ID Sesi: {{ $user->user_id }} (Read-only)</div>
                        </div>
                    </div>

                    <div>
                        <label for="display_name" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $user->display_name) }}" required minlength="2" maxlength="50"
                            oninput="updateSettingsAvatarPreview()" class="w-full px-3.5 py-2 rounded-lg sl-input text-xs sm:text-sm">
                    </div>

                    <div>
                        <label for="about_status" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">About / Status</label>
                        <input type="text" id="about_status" maxlength="100" placeholder="Tersedia untuk komunikasi terenkripsi"
                            oninput="localStorage.setItem('securelink_about', this.value)"
                            class="w-full px-3.5 py-2 rounded-lg sl-input text-xs sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-2">Karakter Avatar</label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach($characterMetadata as $key => $meta)
                                @php
                                    $isSvg = ($key !== 'initial' && isset($characterSvgs[$key]));
                                    $isActive = ($settingsIcon === $key || (isset(\App\Models\UserSession::$legacyAliases[$settingsIcon]) && \App\Models\UserSession::$legacyAliases[$settingsIcon] === $key));
                                @endphp
                                <button type="button" onclick="selectSettingsAvatarPreset('{{ $key }}')" id="settings-preset-btn-{{ $key }}"
                                    class="settings-avatar-btn p-2 rounded-lg border text-center transition flex flex-col items-center gap-1 {{ $isActive ? 'border-[var(--sl-primary-teal)] bg-[var(--sl-active-conv)]' : 'border-[var(--sl-divider)] bg-[var(--sl-chat-bg)] hover:bg-[var(--sl-hover)]' }}">
                                    <div class="w-5 h-5 flex items-center justify-center text-[var(--sl-primary-teal)]">
                                        @if($isSvg) {!! $characterSvgs[$key] !!} @else <span class="text-[11px] font-bold">Aa</span> @endif
                                    </div>
                                    <span class="text-[10px] truncate max-w-full">{{ $meta['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-2">Warna Avatar</label>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @foreach(['#497173', '#71999A', '#EC643E', '#3B8A72', '#20292B', '#2F5D62', '#5E8B7E'] as $c)
                                <label class="cursor-pointer">
                                    <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $settingsColor === $c ? 'checked' : '' }} onchange="updateSettingsAvatarColor('{{ $c }}')">
                                    <div class="w-7 h-7 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-[var(--sl-primary-teal)] transition" style="background-color: {{ $c }};"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-lg sl-btn-coral text-xs font-semibold">Simpan Profil</button>
                </form>
            </section>

            <!-- 4.2 Appearance -->
            <section id="sec-appearance" class="sec-panel space-y-5 {{ $activeSection === 'appearance' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Appearance</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Sesuaikan tema terang, mode gelap berlapis, dan ukuran teks.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-5">
                    <div class="space-y-2">
                        <div class="text-xs font-semibold text-[var(--sl-text-primary)]">Tema Antarmuka</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <button type="button" onclick="setThemeMode('light')" class="p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] text-left text-xs transition">
                                <div class="font-bold text-[var(--sl-text-primary)]">Light Mode</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)]">Mint &amp; Teal cerah</div>
                            </button>
                            <button type="button" onclick="setThemeMode('dark')" class="p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] text-left text-xs transition">
                                <div class="font-bold text-[var(--sl-text-primary)]">Dark Mode</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)]">Slate Teal berlapis</div>
                            </button>
                            <button type="button" onclick="setThemeMode('system')" class="p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] text-left text-xs transition">
                                <div class="font-bold text-[var(--sl-text-primary)]">System Default</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)]">Mengikuti perangkat</div>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-[var(--sl-divider)]">
                        <label for="pref-font-size" class="block text-xs font-semibold text-[var(--sl-text-primary)]">Ukuran Font Chat</label>
                        <select id="pref-font-size" onchange="localStorage.setItem('securelink_font_size', this.value)" class="w-48 px-3 py-2 rounded-lg sl-input text-xs">
                            <option value="small">Kecil</option>
                            <option value="normal" selected>Normal</option>
                            <option value="large">Besar</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- 4.3 Privacy & Security -->
            <section id="sec-security" class="sec-panel space-y-5 {{ $activeSection === 'security' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Privacy &amp; Security</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Informasi kriptografi nyata pada sesi WebRTC SecureLink.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-4 text-xs">
                    <div class="flex items-center justify-between py-2 border-b border-[var(--sl-divider)]">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">WebRTC DataChannel DTLS</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">Enkripsi transport wajib pada seluruh kanal P2P</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] font-semibold">Aktif</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-[var(--sl-divider)]">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">End-to-End Encryption (Web Crypto)</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">ECDH P-256 + AES-GCM 256-bit dibuat per sesi browser</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)] font-semibold">ECDH + AES-GCM</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-[var(--sl-divider)]">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">Peer Verification</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">Pertukaran public key tidak otomatis berarti identitas peer terverifikasi</div>
                        </div>
                        <span class="text-[var(--sl-text-secondary)] font-semibold">Not Verified</span>
                    </div>

                    <div class="pt-1">
                        <label for="sec-ephemeral-default" class="block font-semibold text-[var(--sl-text-primary)] mb-1">Default Ephemeral Message Duration</label>
                        <select id="sec-ephemeral-default" onchange="syncEphemeralDefault(this.value)" class="w-48 px-3 py-2 rounded-lg sl-input text-xs">
                            <option value="0">Nonaktif (Normal)</option>
                            <option value="10">10 Detik</option>
                            <option value="30">30 Detik</option>
                            <option value="60">1 Menit</option>
                            <option value="300">5 Menit</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- 4.4 Notifications -->
            <section id="sec-notifications" class="sec-panel space-y-5 {{ $activeSection === 'notifications' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Notifications</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Atur notifikasi suara dan desktop untuk pesan masuk.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-4 text-xs">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">Sound Notifications</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">Putar nada singkat saat pesan masuk</div>
                        </div>
                        <input type="checkbox" id="pref-sound-notif" onchange="localStorage.setItem('securelink_sound_notif', this.checked)" class="h-4 w-4">
                    </label>

                    <div class="pt-3 border-t border-[var(--sl-divider)] flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">Desktop Notifications</div>
                            <div id="desktop-notif-status" class="text-[11px] text-[var(--sl-text-secondary)]">Status izin browser</div>
                        </div>
                        <button type="button" onclick="requestDesktopPermission()" class="px-3 py-1.5 rounded-lg sl-btn-secondary font-semibold">Minta Izin</button>
                    </div>
                </div>
            </section>

            <!-- 4.5 Connection -->
            <section id="sec-connection" class="sec-panel space-y-5 {{ $activeSection === 'connection' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Connection</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Diagnostik Laravel Reverb, STUN, TURN, dan WebRTC.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-3 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-[var(--sl-divider)]">
                        <span class="text-[var(--sl-text-secondary)]">Signaling Server (Reverb)</span>
                        <span id="conn-signaling-state" class="font-mono font-semibold text-[var(--sl-primary-teal)]">Checking...</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[var(--sl-divider)]">
                        <span class="text-[var(--sl-text-secondary)]">ICE Transport Policy</span>
                        <span id="conn-ice-policy" class="font-mono text-[var(--sl-text-primary)]">-</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[var(--sl-divider)]">
                        <span class="text-[var(--sl-text-secondary)]">STUN Servers</span>
                        <span id="conn-stun-count" class="font-mono text-[var(--sl-text-primary)]">-</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-[var(--sl-text-secondary)]">TURN Relay Fallback</span>
                        <span id="conn-turn-status" class="font-mono text-[var(--sl-text-primary)]">-</span>
                    </div>
                </div>
            </section>

            <!-- 4.6 Chat Preferences -->
            <section id="sec-chat" class="sec-panel space-y-5 {{ $activeSection === 'chat' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">Chat Preferences</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Preferensi pengiriman dan tampilan percakapan.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-4 text-xs">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">Enter to Send</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">Tekan Enter untuk langsung mengirim pesan</div>
                        </div>
                        <input type="checkbox" id="pref-enter-send" checked onchange="localStorage.setItem('securelink_enter_send', this.checked)" class="h-4 w-4">
                    </label>

                    <label class="flex items-center justify-between pt-3 border-t border-[var(--sl-divider)] cursor-pointer">
                        <div>
                            <div class="font-semibold text-[var(--sl-text-primary)]">Message Preview</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)]">Tampilkan pratinjau pesan pada daftar percakapan</div>
                        </div>
                        <input type="checkbox" id="pref-msg-preview" checked onchange="localStorage.setItem('securelink_msg_preview', this.checked)" class="h-4 w-4">
                    </label>
                </div>
            </section>

            <!-- 4.7 About & Help -->
            <section id="sec-about" class="sec-panel space-y-5 {{ $activeSection === 'about' ? '' : 'hidden' }}">
                <div>
                    <h2 class="text-base font-bold text-[var(--sl-text-primary)]">About &amp; Help</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)]">Informasi versi aplikasi dan dokumentasi jaringan.</p>
                </div>

                <div class="sl-surface rounded-xl p-5 space-y-4 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-[var(--sl-divider)]">
                        <span class="text-[var(--sl-text-secondary)]">Versi Aplikasi</span>
                        <span class="font-mono font-semibold text-[var(--sl-text-primary)]">SecureLink v2.2</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('demo') }}" class="px-3 py-1.5 rounded-lg sl-btn-secondary font-semibold">Demonstrasi P2P vs Broadcast</a>
                        <a href="{{ route('tailscale.guide') }}" class="px-3 py-1.5 rounded-lg sl-btn-secondary font-semibold">Panduan VPN Tailscale</a>
                    </div>
                    <form action="{{ route('identity.logout.web') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sesi ini?')" class="pt-3 border-t border-[var(--sl-divider)]">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg sl-btn-coral font-semibold">Keluar Sesi (Logout)</button>
                    </form>
                </div>
            </section>

        </div>
    </main>
</div>

<script>
    const avatarSvgs = @json(\App\Models\UserSession::$avatarSvgs);
    let currentSettingsIcon = "{{ $settingsIcon }}";

    function showSettingsSection(key) {
        document.querySelectorAll('.sec-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('sec-' + key)?.classList.remove('hidden');

        document.querySelectorAll('.sec-nav-btn').forEach(btn => {
            const active = btn.getAttribute('data-sec-btn') === key;
            btn.className = active
                ? 'sec-nav-btn w-full text-left px-3 py-2.5 rounded-lg transition flex flex-col bg-[var(--sl-active-conv)] text-[var(--sl-primary-teal)]'
                : 'sec-nav-btn w-full text-left px-3 py-2.5 rounded-lg transition flex flex-col text-[var(--sl-text-primary)] hover:bg-[var(--sl-hover)]';
        });

        if (key === 'connection') loadConnectionDiagnostics();
    }

    function selectSettingsAvatarPreset(key) {
        currentSettingsIcon = key;
        document.getElementById('settings_avatar_icon').value = key;
        document.querySelectorAll('.settings-avatar-btn').forEach(btn => {
            btn.classList.remove('border-[var(--sl-primary-teal)]', 'bg-[var(--sl-active-conv)]');
            btn.classList.add('border-[var(--sl-divider)]', 'bg-[var(--sl-chat-bg)]');
        });
        const sel = document.getElementById('settings-preset-btn-' + key);
        if (sel) {
            sel.classList.remove('border-[var(--sl-divider)]', 'bg-[var(--sl-chat-bg)]');
            sel.classList.add('border-[var(--sl-primary-teal)]', 'bg-[var(--sl-active-conv)]');
        }
        updateSettingsAvatarPreview();
    }

    function updateSettingsAvatarPreview() {
        const preview = document.getElementById('settings-avatar-preview');
        const name = document.getElementById('display_name')?.value.trim() || '';
        if (currentSettingsIcon !== 'initial' && avatarSvgs[currentSettingsIcon]) {
            preview.innerHTML = '<div class="w-full h-full flex items-center justify-center">' + avatarSvgs[currentSettingsIcon] + '</div>';
        } else {
            preview.innerHTML = '<span>' + (name ? name.charAt(0).toUpperCase() : 'U') + '</span>';
        }
    }

    function updateSettingsAvatarColor(color) {
        document.getElementById('settings-avatar-preview').style.backgroundColor = color;
    }

    function syncEphemeralDefault(val) {
        localStorage.setItem('securelink_ephemeral_default', val);
    }

    function requestDesktopPermission() {
        if (!('Notification' in window)) return;
        Notification.requestPermission().then(perm => {
            const el = document.getElementById('desktop-notif-status');
            if (el) el.textContent = 'Izin: ' + perm;
        });
    }

    async function loadConnectionDiagnostics() {
        const sigEl = document.getElementById('conn-signaling-state');
        if (sigEl) sigEl.textContent = window.EchoConnectionState || 'ready';

        try {
            const res = await fetch('/api/webrtc/ice-servers', { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (data.success) {
                document.getElementById('conn-ice-policy').textContent = data.iceTransportPolicy || 'all';
                document.getElementById('conn-stun-count').textContent = `${data.stunCount || 0} server aktif`;
                document.getElementById('conn-turn-status').textContent = data.hasTurn ? `${data.turnCount} TURN aktif` : 'Belum dikonfigurasi (STUN-only)';
            }
        } catch (e) {}
    }

    document.addEventListener('DOMContentLoaded', () => {
        const aboutEl = document.getElementById('about_status');
        if (aboutEl) aboutEl.value = localStorage.getItem('securelink_about') || '';

        const fontEl = document.getElementById('pref-font-size');
        if (fontEl) fontEl.value = localStorage.getItem('securelink_font_size') || 'normal';

        const ephEl = document.getElementById('sec-ephemeral-default');
        if (ephEl) ephEl.value = localStorage.getItem('securelink_ephemeral_default') || '0';

        const soundEl = document.getElementById('pref-sound-notif');
        if (soundEl) soundEl.checked = localStorage.getItem('securelink_sound_notif') === 'true';

        const desktopEl = document.getElementById('desktop-notif-status');
        if (desktopEl && 'Notification' in window) desktopEl.textContent = 'Izin: ' + Notification.permission;
    });
</script>
@endsection
