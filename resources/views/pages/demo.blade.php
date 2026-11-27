@extends('layouts.app')

@section('title', 'Demonstrasi P2P vs Broadcast — SecureLink')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 py-4">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-[var(--sl-divider)]">
        <div class="space-y-1.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Laboratorium Jaringan</div>
            <h1 class="text-2xl font-bold text-[var(--sl-text-primary)]">Perbandingan Model Komunikasi</h1>
            <p class="text-xs sm:text-sm text-[var(--sl-text-secondary)] max-w-2xl">
                Bandingkan alur pengiriman pesan pada <strong>Server Broadcast Mode</strong> (melalui server pusat) dan <strong>P2P WebRTC Mode</strong> (langsung antarperangkat via DataChannel).
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg sl-btn-secondary text-xs font-semibold shrink-0 self-start sm:self-auto">
            Kembali ke Aplikasi
        </a>
    </div>

    <!-- Live Interactive Packet Test -->
    <section class="sl-surface rounded-xl p-5 sm:p-6 space-y-5">
        <div class="space-y-1">
            <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Pengujian Alur Paket Simulasi</h2>
            <p class="text-xs text-[var(--sl-text-secondary)]">
                Masukkan teks pesan lalu tekan tombol pengujian untuk membandingkan visibilitas payload di server dan jalur transmisi.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2.5">
            <input type="text" id="sim-input" value="Halo, ini paket pengujian keamanan jaringan!" placeholder="Ketik pesan pengujian..."
                class="flex-1 px-3.5 py-2.5 rounded-lg sl-input text-xs sm:text-sm">
            <button type="button" onclick="runLivePacketSimulation()" class="px-5 py-2.5 rounded-lg sl-btn-coral text-xs sm:text-sm font-semibold shrink-0">
                Jalankan Simulasi
            </button>
        </div>

        <!-- Comparison Output Panels -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Server Broadcast Mode -->
            <div class="rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-[var(--sl-divider)] pb-2">
                    <span class="font-bold text-[var(--sl-text-primary)]">1. Server Broadcast Mode</span>
                    <span id="sim-server-state" class="text-[11px] font-mono text-[var(--sl-text-secondary)]">Siap</span>
                </div>
                <div class="text-[11px] font-mono text-[var(--sl-text-secondary)]">
                    Pengirim A &rarr; Server Reverb &rarr; Penerima B, C
                </div>
                <div id="sim-server-log" class="p-3 rounded-lg bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] font-mono text-[11px] leading-relaxed text-[var(--sl-text-secondary)] min-h-[96px]">
                    &gt; Menunggu pengujian dijalankan...
                </div>
                <div id="sim-server-result" class="hidden text-[11px] text-[var(--sl-accent-coral)] font-medium">
                    Server perantara menerima dan meneruskan payload pesan ke peserta room.
                </div>
            </div>

            <!-- P2P WebRTC Mode -->
            <div class="rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-[var(--sl-divider)] pb-2">
                    <span class="font-bold text-[var(--sl-primary-teal)]">2. P2P WebRTC Mode</span>
                    <span id="sim-p2p-state" class="text-[11px] font-mono text-[var(--sl-text-secondary)]">Siap</span>
                </div>
                <div class="text-[11px] font-mono text-[var(--sl-primary-teal)]">
                    Peer A &harr; RTCDataChannel (DTLS + AES-GCM) &harr; Peer B
                </div>
                <div id="sim-p2p-log" class="p-3 rounded-lg bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] font-mono text-[11px] leading-relaxed text-[var(--sl-text-secondary)] min-h-[96px]">
                    &gt; Menunggu pengujian dijalankan...
                </div>
                <div id="sim-p2p-result" class="hidden text-[11px] text-[var(--sl-primary-teal)] font-medium">
                    Server signaling dilewati; isi pesan terenkripsi AES-GCM di browser pengirim dan penerima.
                </div>
            </div>
        </div>
    </section>

    <!-- Concise Comparison Matrix Table -->
    <section class="sl-surface rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-[var(--sl-divider)]">
            <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Ringkasan Perbandingan Teknis</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-[var(--sl-divider)] bg-[var(--sl-main-surface)] text-[var(--sl-text-secondary)]">
                        <th class="py-3 px-4 font-semibold">Aspek</th>
                        <th class="py-3 px-4 font-semibold">Server Broadcast Mode</th>
                        <th class="py-3 px-4 font-semibold text-[var(--sl-primary-teal)]">P2P WebRTC Mode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--sl-divider)] text-[var(--sl-text-primary)]">
                    <tr>
                        <td class="py-3 px-4 font-semibold">Jalur Pesan</td>
                        <td class="py-3 px-4 text-[var(--sl-text-secondary)]">Melalui Server Pusat (Laravel Reverb)</td>
                        <td class="py-3 px-4">RTCDataChannel langsung antar-peer (atau TURN Relay)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Peran Signaling Server</td>
                        <td class="py-3 px-4 text-[var(--sl-text-secondary)]">Menerima dan mem-broadcast isi pesan</td>
                        <td class="py-3 px-4">Hanya menukar SDP Offer/Answer &amp; ICE Candidate</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Enkripsi Transport</td>
                        <td class="py-3 px-4 text-[var(--sl-text-secondary)]">HTTPS / WSS (TLS)</td>
                        <td class="py-3 px-4">DTLS wajib pada transport WebRTC DataChannel</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">End-to-End Encryption</td>
                        <td class="py-3 px-4 text-[var(--sl-accent-coral)] font-medium">Tidak aktif (demonstrasi relay)</td>
                        <td class="py-3 px-4 text-[var(--sl-primary-teal)] font-medium">Aktif (ECDH P-256 + AES-GCM 256-bit)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Dukungan Tailscale VPN</td>
                        <td class="py-3 px-4 text-[var(--sl-text-secondary)]">Akses host signaling pada jaringan privat</td>
                        <td class="py-3 px-4">Koneksi langsung antar-node via IP privat Tailnet</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>

@push('scripts')
<script>
    function runLivePacketSimulation() {
        const rawText = document.getElementById('sim-input').value.trim() || 'Paket pengujian';
        const safeText = rawText.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        document.getElementById('sim-server-state').textContent = 'Relayed via Server';
        document.getElementById('sim-server-log').innerHTML = `
            <div class="text-[var(--sl-text-primary)]">1. [T+10ms] Client A &rarr; Server Reverb:</div>
            <div class="text-[var(--sl-accent-coral)] font-bold pl-3 break-all">"${safeText}"</div>
            <div class="text-[var(--sl-text-primary)] mt-1">2. [T+26ms] Server mem-broadcast payload ke Client B.</div>
        `;
        document.getElementById('sim-server-result').classList.remove('hidden');

        document.getElementById('sim-p2p-state').textContent = 'Direct P2P Encrypted';
        const dummyIv = Math.random().toString(36).substring(2, 14);
        let dummyCipher = 'q3Kj9XvLm7...==';
        try {
            dummyCipher = btoa(unescape(encodeURIComponent(rawText))).substring(0, 22) + '...==';
        } catch (e) {}

        document.getElementById('sim-p2p-log').innerHTML = `
            <div class="text-[var(--sl-text-primary)]">1. [T+2ms] Enkripsi lokal Web Crypto (AES-GCM):</div>
            <div class="text-[var(--sl-primary-teal)] font-bold pl-3 break-all">Cipher: "${dummyCipher}" (IV: ${dummyIv})</div>
            <div class="text-[var(--sl-text-primary)] mt-1">2. [T+8ms] Transmisi langsung via RTCDataChannel &rarr; Client B.</div>
            <div class="text-[var(--sl-text-primary)] mt-1">3. [T+13ms] Dekripsi lokal: <span class="font-bold">"${safeText}"</span></div>
        `;
        document.getElementById('sim-p2p-result').classList.remove('hidden');
    }
</script>
@endpush
@endsection
