@extends('layouts.app')

@section('title', 'SecureLink — Platform Chatting Peer-to-Peer Privat & Aman')

@section('content')
<div class="py-10 sm:py-16 max-w-5xl mx-auto space-y-16">
    <!-- Hero Section -->
    <div class="text-center space-y-6 relative">
        <!-- Verified Security Badge with subtle glow -->
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold bg-white/80 dark:bg-[#111B21]/80 text-[#008069] dark:text-[#25D366] border border-emerald-200/80 dark:border-emerald-500/30 shadow-sm backdrop-blur-md glow-primary">
            <span class="w-2.5 h-2.5 rounded-full bg-[#008069] dark:bg-[#25D366] animate-pulse"></span>
            WebRTC DataChannel &bull; DTLS Transport &bull; E2EE AES-GCM
        </div>

        <!-- Strong Editorial Headline -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#111B21] dark:text-[#E9EDEF] leading-[1.12]">
            Komunikasi P2P Privat <br class="hidden sm:inline">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#008069] via-[#00A884] to-[#25D366]">
                Tanpa Perantara Server
            </span>
        </h1>

        <!-- Editorial Subtitle -->
        <p class="text-base sm:text-lg text-[#667781] dark:text-[#8696A0] leading-relaxed max-w-2xl mx-auto font-normal">
            SecureLink mendemonstrasikan pertukaran data langsung antarperangkat melalui WebRTC DataChannel. Server WebSocket hanya bertindak sebagai signaling negosiasi koneksi tanpa pernah meneruskan atau menyimpan plain-text percakapan Anda.
        </p>

        <!-- CTA Group -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3.5">
            @if(isset($user) && $user)
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-gradient-to-r from-[#008069] to-[#00A884] hover:opacity-95 text-white font-semibold text-sm shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    Masuk ke Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <a href="{{ route('identity.setup') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-gradient-to-r from-[#008069] to-[#00A884] hover:opacity-95 text-white font-semibold text-sm shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    Mulai Chatting Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif
            <a href="{{ route('demo') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl glass-card text-[#111B21] dark:text-[#E9EDEF] font-semibold text-sm transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Demonstrasi P2P vs Broadcast
            </a>
        </div>
    </div>

    <!-- Interactive Architecture Diagram with Glassmorphic Card -->
    <div class="p-6 sm:p-9 rounded-3xl glass-card">
        <div class="text-center max-w-xl mx-auto mb-8">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-emerald-50 dark:bg-emerald-950/60 text-[#008069] dark:text-[#25D366] mb-2">P2P Network Topology</span>
            <h2 class="text-xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Alur Komunikasi Peer-to-Peer</h2>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] mt-1.5 leading-relaxed">
                Visualisasi pemisahan tanggung jawab antara signaling negosiasi dan kanal transfer pesan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-center">
            <!-- Step 1: Peer A -->
            <div class="p-5 rounded-2xl bg-white/70 dark:bg-[#111B21]/70 border border-[#E9EDEF] dark:border-[#222D34] text-center space-y-2.5 transition hover:shadow-md">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-[#008069] text-white flex items-center justify-center font-bold text-base mx-auto shadow-md shadow-emerald-500/20">
                    A
                </div>
                <div class="font-bold text-xs text-[#111B21] dark:text-[#E9EDEF]">Peer Pengirim</div>
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] leading-relaxed">Membuat kunci ECDH, enkripsi pesan dengan AES-GCM, kirim langsung melalui DataChannel.</p>
            </div>

            <!-- Signaling Indicator -->
            <div class="p-5 rounded-2xl bg-emerald-50/50 dark:bg-[#111B21]/60 border border-dashed border-[#008069]/50 dark:border-[#25D366]/50 text-center space-y-2.5">
                <div class="inline-flex p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-[#008069] dark:text-[#25D366]">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="font-bold text-xs text-[#008069] dark:text-[#25D366]">Signaling (WebSocket)</div>
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] leading-relaxed">
                    Hanya menukar SDP Offer/Answer dan ICE Candidate. <span class="font-semibold text-rose-600 dark:text-rose-400">Tidak menerima plain-text.</span>
                </p>
            </div>

            <!-- Step 2: Peer B -->
            <div class="p-5 rounded-2xl bg-white/70 dark:bg-[#111B21]/70 border border-[#E9EDEF] dark:border-[#222D34] text-center space-y-2.5 transition hover:shadow-md">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-600 text-white flex items-center justify-center font-bold text-base mx-auto shadow-md shadow-emerald-500/20">
                    B
                </div>
                <div class="font-bold text-xs text-[#111B21] dark:text-[#E9EDEF]">Peer Penerima</div>
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] leading-relaxed">Menerima ciphertext, verifikasi auth tag, dekripsi pesan, lalu mengirim acknowledgment.</p>
            </div>
        </div>
    </div>

    <!-- Core Pillars Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pillar 1 -->
        <div class="p-6 rounded-3xl glass-card space-y-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-[#008069]/20 text-[#008069] dark:text-[#25D366] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">WebRTC DataChannel &amp; DTLS</h3>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                Jalur koneksi data langsung berlatensi rendah antarperangkat yang diamankan dengan standar protokol kriptografi DTLS.
            </p>
        </div>

        <!-- Pillar 2 -->
        <div class="p-6 rounded-3xl glass-card space-y-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-500/20 text-[#008069] dark:text-[#25D366] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">End-to-End Encryption &amp; PIN</h3>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                Kombinasi Web Crypto API (AES-GCM 256-bit), pesan sementara (ephemeral), serta perlindungan room PIN statis atau Dynamic PIN (TOTP).
            </p>
        </div>

        <!-- Pillar 3 -->
        <div class="p-6 rounded-3xl glass-card space-y-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-500/10 to-purple-500/20 text-[#7657D6] dark:text-[#A58AE8] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Jaringan Privat Tailscale</h3>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                Mendukung skenario pengujian lintas node pada lingkungan VPN privat Tailnet dengan panduan komprehensif.
            </p>
        </div>
    </div>
</div>
@endsection
