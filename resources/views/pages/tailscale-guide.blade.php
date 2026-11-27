@extends('layouts.app')

@section('title', 'Panduan Integrasi VPN Tailscale — SecureLink')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-4">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-[var(--sl-divider)]">
        <div class="space-y-1.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Jaringan Privat Lab</div>
            <h1 class="text-2xl font-bold text-[var(--sl-text-primary)]">Panduan Integrasi VPN Tailscale</h1>
            <p class="text-xs sm:text-sm text-[var(--sl-text-secondary)] max-w-2xl">
                Langkah konfigurasi VPN mesh Tailscale agar dua atau lebih perangkat dapat mengakses signaling server dan membangun koneksi WebRTC P2P dalam jaringan privat.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg sl-btn-secondary text-xs font-semibold shrink-0 self-start sm:self-auto">
            Kembali ke Aplikasi
        </a>
    </div>

    <!-- Step-by-Step Guide -->
    <div class="space-y-4">

        <!-- Step 01 -->
        <section class="sl-surface rounded-xl p-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="px-2 py-0.5 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-mono text-xs font-bold shrink-0">01</span>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Instalasi Tailscale pada Perangkat Pengujian</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                        Pasang aplikasi Tailscale pada seluruh laptop, PC, atau smartphone yang akan digunakan untuk praktikum dari situs resmi atau melalui terminal.
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                <code class="truncate">winget install Tailscale.Tailscale</code>
                <button type="button" onclick="copyTailscaleCmd(this, 'winget install Tailscale.Tailscale')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
            </div>
        </section>

        <!-- Step 02 -->
        <section class="sl-surface rounded-xl p-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="px-2 py-0.5 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-mono text-xs font-bold shrink-0">02</span>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Hubungkan Perangkat ke Tailnet yang Sama</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                        Login menggunakan akun yang sama pada semua perangkat pengujian agar setiap node berada dalam satu jaringan virtual privat (Tailnet).
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                <code class="truncate">tailscale up</code>
                <button type="button" onclick="copyTailscaleCmd(this, 'tailscale up')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
            </div>
        </section>

        <!-- Step 03 -->
        <section class="sl-surface rounded-xl p-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="px-2 py-0.5 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-mono text-xs font-bold shrink-0">03</span>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Catat Alamat IP Tailscale (100.x.y.z)</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                        Setiap perangkat memperoleh IP privat dalam subnet <code>100.64.0.0/10</code>. Periksa IP perangkat yang menjalankan server SecureLink dengan perintah berikut:
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                <code class="truncate">tailscale ip -4</code>
                <button type="button" onclick="copyTailscaleCmd(this, 'tailscale ip -4')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
            </div>
        </section>

        <!-- Step 04 -->
        <section class="sl-surface rounded-xl p-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="px-2 py-0.5 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-mono text-xs font-bold shrink-0">04</span>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Jalankan Server Laravel &amp; Reverb di Host</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                        Bind server web dan WebSocket Reverb ke <code>0.0.0.0</code> agar dapat diakses oleh perangkat peer lain melalui IP Tailscale.
                    </p>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                    <code class="truncate">php artisan serve --host=0.0.0.0 --port=8000</code>
                    <button type="button" onclick="copyTailscaleCmd(this, 'php artisan serve --host=0.0.0.0 --port=8000')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
                </div>
                <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                    <code class="truncate">php artisan reverb:start --host=0.0.0.0 --port=8080</code>
                    <button type="button" onclick="copyTailscaleCmd(this, 'php artisan reverb:start --host=0.0.0.0 --port=8080')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
                </div>
            </div>
        </section>

        <!-- Step 05 -->
        <section class="sl-surface rounded-xl p-5 space-y-3">
            <div class="flex items-start gap-3">
                <span class="px-2 py-0.5 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-mono text-xs font-bold shrink-0">05</span>
                <div class="space-y-1">
                    <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Uji Konektivitas &amp; Buka SecureLink dari Peer</h2>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                        Pastikan kedua node saling menjangkau melalui <code>tailscale ping</code>, lalu buka <code>http://&lt;IP-Tailscale-Host&gt;:8000</code> di browser perangkat kedua dan gabung ke room yang sama.
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] font-mono text-xs text-[var(--sl-text-primary)]">
                <code class="truncate">tailscale status</code>
                <button type="button" onclick="copyTailscaleCmd(this, 'tailscale status')" class="px-2.5 py-1 rounded sl-btn-secondary text-[11px] font-sans font-semibold shrink-0">Copy</button>
            </div>
        </section>
    </div>

    <!-- Troubleshooting Accordion -->
    <section class="sl-surface rounded-xl overflow-hidden text-xs">
        <div class="px-5 py-3.5 border-b border-[var(--sl-divider)] font-bold text-sm text-[var(--sl-text-primary)]">
            Troubleshooting Koneksi Lab
        </div>

        <div class="divide-y divide-[var(--sl-divider)]">
            <div>
                <button type="button" onclick="toggleTsFaq(0)" class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-[var(--sl-hover)] transition">
                    <span class="font-semibold text-[var(--sl-text-primary)]">Perangkat kedua tidak bisa mengakses port 8000 atau 8080</span>
                    <span id="ts-icon-0" class="font-mono text-[var(--sl-text-secondary)]">+</span>
                </button>
                <div id="ts-body-0" class="hidden px-5 pb-4 text-[var(--sl-text-secondary)] leading-relaxed">
                    Pastikan Windows Firewall atau firewall OS mengizinkan koneksi masuk pada port <code>8000</code> dan <code>8080</code>, serta server dijalankan dengan <code>--host=0.0.0.0</code>.
                </div>
            </div>
            <div>
                <button type="button" onclick="toggleTsFaq(1)" class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-[var(--sl-hover)] transition">
                    <span class="font-semibold text-[var(--sl-text-primary)]">Status koneksi menunjukkan TURN Relay bukan Direct P2P</span>
                    <span id="ts-icon-1" class="font-mono text-[var(--sl-text-secondary)]">+</span>
                </button>
                <div id="ts-body-1" class="hidden px-5 pb-4 text-[var(--sl-text-secondary)] leading-relaxed">
                    Periksa apakah Tailscale aktif di kedua perangkat (<code>tailscale ping &lt;ip-peer&gt;</code> menunjukkan koneksi <code>direct</code>, bukan <code>DERP</code>). Jika terhalang NAT simetris, WebRTC memang akan menggunakan TURN Relay.
                </div>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
    function copyTailscaleCmd(btn, cmd) {
        navigator.clipboard.writeText(cmd).then(() => {
            const orig = btn.textContent;
            btn.textContent = 'Disalin';
            window.slToast?.('Perintah terminal disalin.', 'success', 2500);
            setTimeout(() => { btn.textContent = orig; }, 1400);
        });
    }

    function toggleTsFaq(idx) {
        const body = document.getElementById('ts-body-' + idx);
        const icon = document.getElementById('ts-icon-' + idx);
        if (!body) return;
        const hidden = body.classList.contains('hidden');
        body.classList.toggle('hidden', !hidden);
        if (icon) icon.textContent = hidden ? '−' : '+';
    }
</script>
@endpush
@endsection
