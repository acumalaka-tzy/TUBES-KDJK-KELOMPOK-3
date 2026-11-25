@extends('layouts.app')

@section('title', 'SecureLink — Platform Chatting Peer-to-Peer Privat & Aman')

@section('content')
<div class="py-10 sm:py-16">
    <div class="text-center max-w-3xl mx-auto space-y-6">
        <!-- Tag badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            WebRTC DataChannel &bull; DTLS Transport &bull; E2EE AES-GCM
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15]">
            Komunikasi P2P Privat <br class="hidden sm:inline">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">Tanpa Perantara Server</span>
        </h1>

        <!-- Subtitle -->
        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl mx-auto">
            SecureLink mendemonstrasikan pertukaran data langsung antarperangkat melalui WebRTC DataChannel. Server signaling hanya membantu inisialisasi koneksi tanpa pernah menyimpan plain-text chat.
        </p>

        <!-- CTA Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            @if(isset($user) && $user)
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                    Masuk ke Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <a href="{{ route('identity.setup') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                    Mulai Chatting Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif
            <a href="{{ route('demo') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] hover:bg-slate-50 dark:hover:bg-[#1f293d] text-slate-700 dark:text-slate-200 font-semibold text-sm transition">
                Buka Mode Demonstrasi
            </a>
        </div>
    </div>

    <!-- Feature Highlights Grid -->
    <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <!-- Card 1 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">WebRTC DataChannel</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Pesan ditransmisikan secara langsung antar-peer secara bilateral atau melalui TURN relay saat NAT traversal terbatas.
            </p>
        </div>

        <!-- Card 2 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Perlindungan Berlapis &amp; PIN</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Kombinasi DTLS, E2EE AES-GCM, Room PIN statis atau Dynamic PIN (TOTP), serta fitur pesan sementara (ephemeral).
            </p>
        </div>

        <!-- Card 3 -->
        <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] shadow-sm flex flex-col">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Demonstrasi Broadcast vs P2P</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Bandingkan secara transparan bagaimana pengiriman melalui relay server konvensional berbeda dengan enkripsi transport P2P.
            </p>
        </div>
    </div>
</div>
@endsection
