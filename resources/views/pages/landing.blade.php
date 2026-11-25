@extends('layouts.app')

@section('title', 'SecureLink — Platform Chatting Peer-to-Peer Privat & Aman')

@section('content')
<div class="py-12 sm:py-20 max-w-5xl mx-auto space-y-16">
    <!-- Hero Section -->
    <div class="text-center space-y-6">
        <!-- Verified Security Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/50 text-[#315CE8] dark:text-[#7895FF] border border-blue-200 dark:border-blue-800/60 shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-[#315CE8] dark:bg-[#7895FF] animate-pulse"></span>
            WebRTC DataChannel &bull; DTLS Transport &bull; E2EE AES-GCM
        </div>

        <!-- Strong Editorial Headline -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#182033] dark:text-[#F1F4FA] leading-[1.15]">
            Komunikasi P2P Privat <br class="hidden sm:inline">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#315CE8] to-[#7657D6] dark:from-[#7895FF] dark:to-[#A58AE8]">
                Tanpa Perantara Server
            </span>
        </h1>

        <!-- Editorial Subtitle -->
        <p class="text-base sm:text-lg text-[#667085] dark:text-[#A8B2C3] leading-relaxed max-w-2xl mx-auto">
            SecureLink mendemonstrasikan pertukaran data langsung antarperangkat melalui WebRTC DataChannel. Server WebSocket hanya bertindak sebagai signaling negosiasi koneksi tanpa pernah meneruskan atau menyimpan plain-text percakapan Anda.
        </p>

        <!-- CTA Group -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            @if(isset($user) && $user)
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                    Masuk ke Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <a href="{{ route('identity.setup') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                    Mulai Chatting Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif
            <a href="{{ route('demo') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] text-[#182033] dark:text-[#F1F4FA] font-semibold text-sm transition flex items-center justify-center gap-2">
                Demonstrasi P2P vs Broadcast
            </a>
        </div>
    </div>

    <!-- Interactive Architecture Diagram -->
    <div class="p-6 sm:p-8 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-sm">
        <div class="text-center max-w-xl mx-auto mb-8">
            <h2 class="text-lg font-bold text-[#182033] dark:text-[#F1F4FA]">Alur Komunikasi Peer-to-Peer</h2>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1">
                Visualisasi pemisahan tanggung jawab antara signaling negosiasi dan kanal transfer pesan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <!-- Step 1: Peer A -->
            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-950/60 text-[#315CE8] dark:text-[#7895FF] flex items-center justify-center font-bold mx-auto">
                    A
                </div>
                <div class="font-bold text-xs text-[#182033] dark:text-[#F1F4FA]">Peer Pengirim</div>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">Membuat kunci ECDH, enkripsi pesan dengan AES-GCM, kirim melalui DataChannel.</p>
            </div>

            <!-- Signaling Indicator -->
            <div class="p-4 rounded-xl bg-white dark:bg-[#151A24] border border-dashed border-[#315CE8]/40 dark:border-[#7895FF]/40 text-center space-y-2">
                <div class="inline-flex p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-[#315CE8] dark:text-[#7895FF]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="font-bold text-xs text-[#315CE8] dark:text-[#7895FF]">Signaling (WebSocket)</div>
                <p class="text-[10px] text-[#667085] dark:text-[#A8B2C3]">
                    Hanya menukar SDP Offer/Answer dan ICE Candidate. <strong>Tidak menerima plain-text.</strong>
                </p>
            </div>

            <!-- Step 2: Peer B -->
            <div class="p-4 rounded-xl bg-[#EEF1F7]/50 dark:bg-[#222B3A] border border-[#E3E7EF] dark:border-[#344052] text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-950/60 text-[#7657D6] dark:text-[#A58AE8] flex items-center justify-center font-bold mx-auto">
                    B
                </div>
                <div class="font-bold text-xs text-[#182033] dark:text-[#F1F4FA]">Peer Penerima</div>
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3]">Menerima ciphertext, verifikasi auth tag, dekripsi pesan, lalu mengirim acknowledgment.</p>
            </div>
        </div>
    </div>

    <!-- Core Pillars Grid (PRD Section 1.3 & 1.4) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pillar 1 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-2xs space-y-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-[#315CE8] dark:text-[#7895FF] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#182033] dark:text-[#F1F4FA]">WebRTC DataChannel &amp; DTLS</h3>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] leading-relaxed">
                Jalur koneksi data langsung berlatensi rendah antarperangkat yang diamankan dengan standar protokol kriptografi DTLS.
            </p>
        </div>

        <!-- Pillar 2 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-2xs space-y-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-[#55C49A] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#182033] dark:text-[#F1F4FA]">End-to-End Encryption &amp; PIN</h3>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] leading-relaxed">
                Kombinasi Web Crypto API (AES-GCM 256-bit), pesan sementara (ephemeral), serta perlindungan room PIN statis atau Dynamic PIN (TOTP).
            </p>
        </div>

        <!-- Pillar 3 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] shadow-2xs space-y-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-[#7657D6] dark:text-[#A58AE8] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#182033] dark:text-[#F1F4FA]">Jaringan Privat Tailscale</h3>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] leading-relaxed">
                Mendukung skenario pengujian lintas node pada lingkungan VPN privat Tailnet dengan panduan komprehensif.
            </p>
        </div>
    </div>
</div>
@endsection
