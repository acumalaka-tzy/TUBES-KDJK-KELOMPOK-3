@extends('layouts.app')

@section('title', 'Mode Demonstrasi Jaringan — SecureLink')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-4">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800">
            Praktikum Komunikasi Data &amp; Jaringan Komputer
        </span>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Perbandingan Model Komunikasi</h1>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            Halaman ini mendemonstrasikan perbedaan mendasar arsitektur pengiriman data berbasis <strong>Server Broadcast</strong> dibandingkan dengan <strong>Peer-to-Peer (WebRTC)</strong>.
        </p>
    </div>

    <!-- Interactive Comparison Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Column A: Server-Based Broadcast -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Mode Konvensional</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-semibold">Server-Centric</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">1. Server-Based Broadcast</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                    Setiap pesan harus singgah ke server pusat sebelum diteruskan (relayed) ke semua peserta room lainnya.
                </p>

                <!-- Flow Visualization -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0b0f19] border border-slate-200 dark:border-[#1f293d] space-y-2.5 text-xs font-mono">
                    <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                        <span>[Pengirim A]</span>
                        <span class="text-amber-500">&xrarr; Kirim Pesan &xrarr;</span>
                        <span class="font-bold text-amber-600">[Central Server]</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                        <span class="font-bold text-amber-600">[Central Server]</span>
                        <span class="text-amber-500">&xrarr; Broadcast &xrarr;</span>
                        <span>[Penerima B, C, ...]</span>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500">&bull;</span>
                        <span><strong>Titik Ketergantungan:</strong> Server memproses dan dapat membaca payload pesan jika tidak di-E2EE.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500">&bull;</span>
                        <span><strong>Beban Server:</strong> Meningkat linear terhadap perkalian jumlah peserta yang terhubung.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Column B: Peer-to-Peer WebRTC -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-blue-200 dark:border-blue-900/60 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Mode SecureLink</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-semibold">P2P Mesh / Bilateral</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">2. WebRTC Peer-to-Peer (DataChannel)</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                    Setelah negosiasi SDP/ICE selesai via signaling server, data ditransmisikan langsung antarperangkat melalui DataChannel.
                </p>

                <!-- Flow Visualization -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#0b0f19] border border-blue-200 dark:border-blue-900/40 space-y-2.5 text-xs font-mono">
                    <div class="flex items-center justify-between text-slate-500 text-[11px]">
                        <span>[Peer A]</span>
                        <span>&harr; SDP/ICE Signaling &harr;</span>
                        <span>[Signaling Server]</span>
                    </div>
                    <div class="flex items-center justify-between text-blue-600 dark:text-blue-400 font-bold">
                        <span>[Peer A]</span>
                        <span>&xlarr; DTLS Encrypted DataChannel &xrarr;</span>
                        <span>[Peer B]</span>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">&bull;</span>
                        <span><strong>Privasi Tertinggi:</strong> Isi pesan tidak pernah melewati atau disimpan di server pada koneksi P2P.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">&bull;</span>
                        <span><strong>Transport Security:</strong> Dilindungi enkripsi bawaan DTLS pada lapisan WebRTC transport + E2EE AES-GCM.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PRD Section 9.3 Comparison Matrix Table -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-wider">
            Matriks Perbandingan Teknis (PRD Section 9.3)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-[#1f293d] text-slate-400">
                        <th class="py-3 px-4 font-bold">Aspek</th>
                        <th class="py-3 px-4 font-bold text-amber-600 dark:text-amber-400">Server Broadcast Mode</th>
                        <th class="py-3 px-4 font-bold text-blue-600 dark:text-blue-400">P2P WebRTC Mode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#1f293d]/50 text-slate-700 dark:text-slate-300">
                    <tr>
                        <td class="py-3 px-4 font-semibold">Jalur Pesan</td>
                        <td class="py-3 px-4">Melalui Server Pusat</td>
                        <td class="py-3 px-4 font-semibold text-blue-600 dark:text-blue-400">DataChannel Langsung antar-peer (atau TURN relay)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Signaling Server</td>
                        <td class="py-3 px-4">Tidak selalu diperlukan</td>
                        <td class="py-3 px-4">Diperlukan (SDP Offer/Answer &amp; ICE Candidate)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Server Meneruskan Isi Pesan</td>
                        <td class="py-3 px-4 text-rose-500 font-semibold">Ya (Server menjadi perantara)</td>
                        <td class="py-3 px-4 text-emerald-500 font-semibold">Tidak pada mode P2P</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Enkripsi Transport DTLS</td>
                        <td class="py-3 px-4">Bergantung konfigurasi transport</td>
                        <td class="py-3 px-4 font-semibold text-emerald-500">Wajib Digunakan WebRTC DataChannel</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">End-to-End Encryption (E2EE)</td>
                        <td class="py-3 px-4">Dapat ditambahkan</td>
                        <td class="py-3 px-4 font-semibold text-blue-600 dark:text-blue-400">AES-GCM (Browser Web Crypto API)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-semibold">Dukungan Tailscale VPN</td>
                        <td class="py-3 px-4">Akses signaling / host</td>
                        <td class="py-3 px-4">Akses privat antar-peer pada skenario lab</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
