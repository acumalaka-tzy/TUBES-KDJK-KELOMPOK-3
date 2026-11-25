@extends('layouts.app')

@section('title', 'Mode Demonstrasi Jaringan — SecureLink')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-4">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <span class="text-[10px] font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-widest px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60">
            Praktikum Komunikasi Data &amp; Jaringan Komputer
        </span>
        <h1 class="text-3xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Perbandingan Model Komunikasi</h1>
        <p class="text-xs sm:text-sm text-[#667781] dark:text-[#8696A0] leading-relaxed">
            Halaman ini mendemonstrasikan perbedaan mendasar arsitektur pengiriman data berbasis <strong>Server Broadcast</strong> dibandingkan dengan <strong>Peer-to-Peer (WebRTC)</strong>.
        </p>
    </div>

    <!-- Interactive Comparison Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Column A: Server-Based Broadcast -->
        <div class="p-6 rounded-2xl glass-card border border-[#E9EDEF] dark:border-[#222D34] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-amber-600 dark:text-[#E7B45B] uppercase tracking-wider">Mode Konvensional</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-[#E7B45B] font-semibold border border-amber-200 dark:border-amber-800/50">
                        Server-Centric
                    </span>
                </div>
                <h3 class="text-lg font-bold text-[#111B21] dark:text-[#E9EDEF] mb-2">1. Server-Based Broadcast</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed mb-4">
                    Setiap pesan harus singgah ke server pusat sebelum diteruskan (relayed) ke semua peserta room lainnya.
                </p>

                <!-- Flow Visualization -->
                <div class="p-4 rounded-xl bg-[#F0F2F5]/70 dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] space-y-2.5 text-xs font-mono">
                    <div class="flex items-center justify-between text-[#111B21] dark:text-[#E9EDEF]">
                        <span>[Pengirim A]</span>
                        <span class="text-amber-500">&xrarr; Kirim Pesan &xrarr;</span>
                        <span class="font-bold text-amber-600 dark:text-[#E7B45B]">[Central Server]</span>
                    </div>
                    <div class="flex items-center justify-between text-[#111B21] dark:text-[#E9EDEF]">
                        <span class="font-bold text-amber-600 dark:text-[#E7B45B]">[Central Server]</span>
                        <span class="text-amber-500">&xrarr; Broadcast &xrarr;</span>
                        <span>[Penerima B, C, ...]</span>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-xs text-[#667781] dark:text-[#8696A0]">
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">&bull;</span>
                        <span><strong>Titik Ketergantungan:</strong> Server memproses dan dapat membaca payload pesan jika tidak di-E2EE.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">&bull;</span>
                        <span><strong>Beban Server:</strong> Meningkat linear terhadap perkalian jumlah peserta yang terhubung.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Column B: Peer-to-Peer WebRTC -->
        <div class="p-6 rounded-2xl glass-card border border-[#008069]/40 dark:border-[#25D366]/40 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-[#008069] dark:text-[#25D366] uppercase tracking-wider">Mode SecureLink</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-[#008069] dark:text-[#25D366] font-semibold border border-emerald-200 dark:border-emerald-800/50">
                        P2P Mesh / Bilateral
                    </span>
                </div>
                <h3 class="text-lg font-bold text-[#111B21] dark:text-[#E9EDEF] mb-2">2. WebRTC Peer-to-Peer (DataChannel)</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed mb-4">
                    Setelah negosiasi SDP/ICE selesai via signaling server, data ditransmisikan langsung antarperangkat melalui DataChannel.
                </p>

                <!-- Flow Visualization -->
                <div class="p-4 rounded-xl bg-[#F0F2F5]/70 dark:bg-[#111B21] border border-emerald-200 dark:border-emerald-900/40 space-y-2.5 text-xs font-mono">
                    <div class="flex items-center justify-between text-[#667781] dark:text-[#8696A0] text-[11px]">
                        <span>[Peer A]</span>
                        <span>&harr; SDP/ICE Signaling &harr;</span>
                        <span>[Signaling Server]</span>
                    </div>
                    <div class="flex items-center justify-between text-[#008069] dark:text-[#25D366] font-bold">
                        <span>[Peer A]</span>
                        <span>&xlarr; DTLS Encrypted DataChannel &xrarr;</span>
                        <span>[Peer B]</span>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-xs text-[#667781] dark:text-[#8696A0]">
                    <li class="flex items-start gap-2">
                        <span class="text-[#008069] dark:text-[#25D366] font-bold">&bull;</span>
                        <span><strong>Privasi Tertinggi:</strong> Isi pesan tidak pernah melewati atau disimpan di server pada koneksi P2P.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-[#008069] dark:text-[#25D366] font-bold">&bull;</span>
                        <span><strong>Transport Security:</strong> Dilindungi enkripsi bawaan DTLS pada lapisan WebRTC transport + E2EE AES-GCM.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PRD Section 9.3 Comparison Matrix Table -->
    <div class="glass-card border border-[#E9EDEF] dark:border-[#222D34] rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF] mb-4 uppercase tracking-wider">
            Matriks Perbandingan Teknis (PRD Section 9.3)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E9EDEF] dark:border-[#222D34] text-[#667781] dark:text-[#8696A0]">
                        <th class="py-3 px-4 font-bold">Aspek</th>
                        <th class="py-3 px-4 font-bold text-amber-600 dark:text-[#E7B45B]">Server Broadcast Mode</th>
                        <th class="py-3 px-4 font-bold text-[#008069] dark:text-[#25D366]">P2P WebRTC Mode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E9EDEF]/60 dark:divide-[#222D34]/60 text-[#111B21] dark:text-[#E9EDEF]">
                    <tr>
                        <td class="py-3 px-4 font-semibold">Jalur Pesan</td>
                        <td class="py-3 px-4 text-[#667781] dark:text-[#8696A0]">Melalui Server Pusat</td>
                        <td class="py-3 px-4 font-semibold text-[#008069] dark:text-[#25D366]">DataChannel Langsung antar-peer (atau TURN relay)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Signaling Server</td>
                        <td class="py-3 px-4 text-[#667781] dark:text-[#8696A0]">Tidak selalu diperlukan</td>
                        <td class="py-3 px-4">Diperlukan (SDP Offer/Answer &amp; ICE Candidate)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Server Meneruskan Isi Pesan</td>
                        <td class="py-3 px-4 text-rose-500 font-semibold">Ya (Server menjadi perantara)</td>
                        <td class="py-3 px-4 text-emerald-600 dark:text-[#25D366] font-semibold">Tidak pada mode P2P</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Enkripsi Transport DTLS</td>
                        <td class="py-3 px-4 text-[#667781] dark:text-[#8696A0]">Bergantung konfigurasi transport</td>
                        <td class="py-3 px-4 font-semibold text-emerald-600 dark:text-[#25D366]">Wajib Digunakan WebRTC DataChannel</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">End-to-End Encryption (E2EE)</td>
                        <td class="py-3 px-4 text-[#667781] dark:text-[#8696A0]">Dapat ditambahkan</td>
                        <td class="py-3 px-4 font-semibold text-[#008069] dark:text-[#25D366]">AES-GCM (Browser Web Crypto API)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Dukungan Tailscale VPN</td>
                        <td class="py-3 px-4 text-[#667781] dark:text-[#8696A0]">Akses signaling / host</td>
                        <td class="py-3 px-4">Akses privat antar-peer pada skenario lab</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PRD Section 10.13 Live Interactive Simulator -->
    <div class="glass-card border border-[#E9EDEF] dark:border-[#222D34] rounded-2xl p-6 shadow-sm space-y-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-[#008069] dark:text-[#25D366] text-[10px] font-bold uppercase tracking-wider mb-1">
                Laboratorium Praktikum
            </div>
            <h3 class="text-base font-bold text-[#111B21] dark:text-[#E9EDEF]">Simulator Alur Paket Real-Time</h3>
            <p class="text-xs text-[#667781] dark:text-[#8696A0]">
                Ketik teks simulasi dan amati perbedaan inspeksi paket antara Relay Server dan DataChannel P2P secara langsung.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" id="sim-input" value="Halo, ini adalah paket rahasia pengujian jaringan!" placeholder="Ketik pesan simulasi..."
                class="flex-1 px-3.5 py-2.5 rounded-xl border border-[#E9EDEF] dark:border-[#222D34] bg-white dark:bg-[#111B21] text-xs sm:text-sm text-[#111B21] dark:text-[#E9EDEF] focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884]">
            <button type="button" onclick="runLivePacketSimulation()" class="px-5 py-2.5 rounded-xl bg-[#008069] hover:bg-[#00A884] text-white font-semibold text-xs shadow-sm transition shrink-0 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Jalankan Simulasi
            </button>
        </div>

        <!-- Simulation Comparison Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
            <!-- Simulator Box A: Server Broadcast -->
            <div class="p-4 rounded-xl bg-[#F0F2F5]/70 dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] space-y-3">
                <div class="flex items-center justify-between font-bold text-amber-600 dark:text-[#E7B45B]">
                    <span>[SERVER BROADCAST LOG]</span>
                    <span id="sim-server-state" class="text-[10px] text-slate-400">Siap</span>
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-[#202C33] border border-[#E9EDEF] dark:border-[#222D34] space-y-1 text-[11px] leading-relaxed">
                    <div class="text-slate-400">&gt; Menunggu transmisi...</div>
                    <div id="sim-server-log" class="text-[#111B21] dark:text-[#E9EDEF]"></div>
                </div>
                <div id="sim-server-result" class="hidden p-2 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 text-[11px]">
                    ⚠️ <strong>Server Vulnerability:</strong> Server perantara dapat menginspeksi plain-text pesan karena data singgah di memori server.
                </div>
            </div>

            <!-- Simulator Box B: WebRTC P2P -->
            <div class="p-4 rounded-xl bg-emerald-50/40 dark:bg-[#202C33]/40 border border-emerald-200 dark:border-emerald-900/40 space-y-3">
                <div class="flex items-center justify-between font-bold text-[#008069] dark:text-[#25D366]">
                    <span>[WEBRTC P2P DATACHANNEL]</span>
                    <span id="sim-p2p-state" class="text-[10px] text-slate-400">Siap</span>
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] space-y-1 text-[11px] leading-relaxed">
                    <div class="text-slate-400">&gt; Menunggu transmisi...</div>
                    <div id="sim-p2p-log" class="text-[#111B21] dark:text-[#E9EDEF]"></div>
                </div>
                <div id="sim-p2p-result" class="hidden p-2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-[#25D366] text-[11px]">
                    🔒 <strong>Privacy Guaranteed:</strong> Server tidak menerima paket chat. DataChannel terenkripsi DTLS + E2EE AES-GCM bilateral.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function runLivePacketSimulation() {
        const rawText = document.getElementById('sim-input').value.trim() || 'Paket pengujian';
        const safeText = rawText.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        // Animate Server side
        document.getElementById('sim-server-state').textContent = 'Memproses...';
        document.getElementById('sim-server-log').innerHTML = `
            <div>1. [T+12ms] Client A &xrarr; Server: Menerima payload plain-text:</div>
            <div class="text-rose-600 dark:text-[#F07878] font-bold pl-3 truncate">"${safeText}"</div>
            <div>2. [T+28ms] Server memvalidasi dan mem-broadcast ke Client B...</div>
        `;
        document.getElementById('sim-server-result').classList.remove('hidden');

        // Animate P2P side with crypto simulation
        document.getElementById('sim-p2p-state').textContent = 'Mengenkripsi...';
        const dummyIv = Math.random().toString(36).substring(2, 14);
        let dummyCipher = '';
        try {
            dummyCipher = btoa(unescape(encodeURIComponent(rawText))).substring(0, 24) + '...==';
        } catch (e) {
            dummyCipher = 'q3Kj9XvLm7...==';
        }

        document.getElementById('sim-p2p-log').innerHTML = `
            <div>1. [T+2ms] Client A mengenkripsi teks lokal via Web Crypto (AES-GCM):</div>
            <div class="text-[#008069] dark:text-[#25D366] font-bold pl-3 break-all">Cipher: "${dummyCipher}" (IV: ${dummyIv})</div>
            <div>2. [T+9ms] Transmisi langsung via RTCDataChannel &xrarr; Client B (Server di-bypass).</div>
            <div>3. [T+14ms] Client B memverifikasi tag otentikasi &amp; mendekripsi pesan:</div>
            <div class="text-emerald-600 dark:text-[#25D366] font-bold pl-3 truncate">Plaintext: "${safeText}"</div>
        `;
        document.getElementById('sim-p2p-result').classList.remove('hidden');
    }
</script>
@endpush
@endsection
