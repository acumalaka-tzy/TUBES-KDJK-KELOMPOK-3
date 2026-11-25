@extends('layouts.app')

@section('title', 'Panduan Jaringan Privat Tailscale — SecureLink')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-4">
    <!-- Header -->
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-[#008069] dark:text-[#25D366] text-xs font-semibold">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            PRD Section 3.6 Compliance
        </div>
        <h1 class="text-3xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Panduan Integrasi VPN Tailscale</h1>
        <p class="text-xs sm:text-sm text-[#667781] dark:text-[#8696A0] leading-relaxed">
            Petunjuk konfigurasi jaringan privat virtual (VPN mesh) menggunakan Tailscale untuk pengujian laboratorium antarperangkat.
        </p>
    </div>

    <!-- 4 Step Implementation Guide -->
    <div class="space-y-4">
        <!-- Step 1 -->
        <div class="p-6 rounded-2xl glass-card border border-[#E9EDEF] dark:border-[#222D34] shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-[#008069] dark:text-[#25D366] flex items-center justify-center font-bold text-sm shrink-0">
                1
            </div>
            <div class="space-y-2">
                <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Instalasi Tailscale pada Seluruh Perangkat Lab</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                    Unduh dan pasang aplikasi Tailscale resmi pada masing-masing perangkat pengujian (Laptop, PC, atau Smartphone).
                </p>
                <div class="p-3 rounded-xl bg-[#F0F2F5]/70 dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] text-xs font-mono text-[#111B21] dark:text-[#E9EDEF]">
                    # Windows / macOS / Linux / Android / iOS<br>
                    https://tailscale.com/download
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="p-6 rounded-2xl glass-card border border-[#E9EDEF] dark:border-[#222D34] shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-[#7657D6] dark:text-[#A58AE8] flex items-center justify-center font-bold text-sm shrink-0">
                2
            </div>
            <div class="space-y-2">
                <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Login ke Tailnet yang Sama</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                    Pastikan kedua atau seluruh perangkat anggota kelompok login menggunakan akun SSO (Google / Microsoft / GitHub) yang sama agar tergabung dalam satu <strong>Tailnet</strong> privat.
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="p-6 rounded-2xl glass-card border border-[#E9EDEF] dark:border-[#222D34] shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-[#25D366] flex items-center justify-center font-bold text-sm shrink-0">
                3
            </div>
            <div class="space-y-2">
                <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Catat Alamat IP Tailscale (100.x.y.z)</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                    Setiap perangkat akan mendapatkan alamat IP privat statis dalam rentang subnet <code>100.64.0.0/10</code> (CGNAT). Periksa IP perangkat host signaling server menggunakan terminal:
                </p>
                <div class="p-3 rounded-xl bg-[#F0F2F5]/70 dark:bg-[#111B21] border border-[#E9EDEF] dark:border-[#222D34] text-xs font-mono text-[#111B21] dark:text-[#E9EDEF]">
                    tailscale ip -4<br>
                    # Output contoh: 100.84.192.45
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="p-6 rounded-2xl glass-card border border-[#E9EDEF] dark:border-[#222D34] shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-[#E7B45B] flex items-center justify-center font-bold text-sm shrink-0">
                4
            </div>
            <div class="space-y-2">
                <h3 class="text-sm font-bold text-[#111B21] dark:text-[#E9EDEF]">Jalankan Server dan Lakukan Peer Connection</h3>
                <p class="text-xs text-[#667781] dark:text-[#8696A0] leading-relaxed">
                    Buka peramban di perangkat kedua menuju alamat Tailscale host (misalnya <code>http://100.84.192.45:8000</code>). Masuk ke room yang sama dan amati koneksi P2P terbentuk secara langsung via VPN privat tanpa melalui internet publik.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
