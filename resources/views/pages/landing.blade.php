@extends('layouts.app')

@section('title', 'SecureLink — Private Peer-to-Peer Messaging')

@section('content')
<div class="py-8 sm:py-12 max-w-5xl mx-auto space-y-12">

    <!-- Compact Editorial Hero -->
    <section class="text-center max-w-2xl mx-auto space-y-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] text-xs font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-[#497173]"></span>
            <span>WebRTC DataChannel &middot; DTLS &middot; E2EE AES-GCM</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-[var(--sl-text-primary)] leading-[1.15]">
            Komunikasi Peer-to-Peer Privat, Langsung Antarperangkat.
        </h1>

        <p class="text-sm sm:text-base text-[var(--sl-text-secondary)] leading-relaxed">
            SecureLink menghubungkan perangkat secara langsung melalui WebRTC DataChannel dengan enkripsi ujung-ke-ujung (Web Crypto API). Server signaling hanya membantu negosiasi koneksi tanpa pernah meneruskan atau menyimpan isi percakapan Anda.
        </p>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ (isset($user) && $user) ? route('dashboard') : route('identity.setup') }}"
               class="w-full sm:w-auto px-6 py-2.5 rounded-lg sl-btn-coral font-semibold text-sm inline-flex items-center justify-center gap-2">
                Open SecureLink
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            <a href="{{ route('demo') }}"
               class="w-full sm:w-auto px-6 py-2.5 rounded-lg sl-btn-secondary font-semibold text-sm inline-flex items-center justify-center">
                Demonstrasi P2P vs Broadcast
            </a>
        </div>
    </section>

    <!-- Realistic Product Application Preview -->
    <section class="sl-surface rounded-xl overflow-hidden shadow-sm">
        <!-- Window Chrome -->
        <div class="px-4 py-2.5 bg-[var(--sl-main-surface)] border-b border-[var(--sl-divider)] flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#EC643E]/70"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#71999A]/70"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#497173]/70"></span>
            </div>
            <span class="text-[11px] font-mono text-[var(--sl-text-secondary)]">securelink.local / P2P Encrypted Workspace</span>
            <span class="text-[11px] font-semibold text-[var(--sl-primary-teal)]">DTLS + AES-GCM</span>
        </div>

        <!-- 3-Pane Preview Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 min-h-[300px]">
            <!-- Left Navigation Rail -->
            <div class="hidden md:flex md:col-span-1 bg-[#497173] text-white flex-col items-center py-4 gap-4">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="w-8 h-8 rounded-lg opacity-75 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div class="w-8 h-8 rounded-lg opacity-75 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>

            <!-- Conversation Sidebar -->
            <div class="md:col-span-4 border-b md:border-b-0 md:border-r border-[var(--sl-divider)] bg-[var(--sl-white-surface)] p-3 space-y-2">
                <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Private Chats</div>
                <div class="px-2.5 py-2 rounded-lg bg-[var(--sl-soft-teal)] flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#497173] text-white text-xs font-bold flex items-center justify-center shrink-0">R</div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">Raka Pratama</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">Direct P2P aktif</div>
                        </div>
                    </div>
                    <span class="text-[10px] text-[var(--sl-text-secondary)]">09:42</span>
                </div>

                <div class="px-2 pt-2 text-[11px] font-bold uppercase tracking-wider text-[var(--sl-text-secondary)]">Group Chats</div>
                <div class="px-2.5 py-2 rounded-lg hover:bg-[var(--sl-main-surface)] flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#71999A] text-white text-xs font-bold flex items-center justify-center shrink-0">K3</div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">Lab Jaringan Kelompok 3</div>
                            <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">4 anggota terhubung</div>
                        </div>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-[#EC643E] shrink-0"></span>
                </div>
            </div>

            <!-- Main Conversation Area -->
            <div class="md:col-span-7 bg-[var(--sl-main-surface)] flex flex-col justify-between p-4 space-y-3">
                <div class="flex items-center justify-between pb-2.5 border-b border-[var(--sl-divider)]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#497173] text-white text-xs font-bold flex items-center justify-center">R</div>
                        <div>
                            <div class="text-xs font-bold text-[var(--sl-text-primary)]">Raka Pratama</div>
                            <div class="text-[11px] text-[var(--sl-primary-teal)] font-medium">Direct P2P &middot; E2EE Active</div>
                        </div>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] text-[var(--sl-text-secondary)]">QR Invite</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-start">
                        <div class="max-w-[78%] px-3 py-2 rounded-xl rounded-tl-sm bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] text-[var(--sl-text-primary)]">
                            <div>ICE candidate selesai, DataChannel sudah terbuka langsung antarperangkat.</div>
                            <div class="text-[10px] text-[var(--sl-text-secondary)] mt-1 text-right">09:41</div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <div class="max-w-[78%] px-3 py-2 rounded-xl rounded-tr-sm bg-[var(--sl-soft-teal)] text-[var(--sl-text-primary)]">
                            <div>Terverifikasi. Pesan terenkripsi AES-GCM tanpa melewati memori server.</div>
                            <div class="text-[10px] text-[var(--sl-primary-teal)] mt-1 text-right">09:42 &middot; Delivered</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-[var(--sl-divider)] flex items-center gap-2">
                    <div class="flex-1 px-3 py-1.5 rounded-lg bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] text-xs text-[var(--sl-text-secondary)]">
                        Tulis pesan terenkripsi...
                    </div>
                    <div class="px-3 py-1.5 rounded-lg bg-[#EC643E] text-white text-xs font-semibold">Kirim</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Capabilities: Private Chat, Group Chat, End-to-End Encryption -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="sl-surface rounded-xl p-5 space-y-2">
            <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Private Chat</h2>
            <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                Komunikasi 1-lawan-1 langsung melalui WebRTC DataChannel dengan deteksi jalur Direct P2P atau TURN Relay secara akurat.
            </p>
        </div>

        <div class="sl-surface rounded-xl p-5 space-y-2">
            <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">Group Chat</h2>
            <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                Grup P2P Mesh dengan manajemen anggota, kontrol admin, PIN akses, serta undangan berbasis token QR Code yang dapat dicabut.
            </p>
        </div>

        <div class="sl-surface rounded-xl p-5 space-y-2">
            <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="text-sm font-bold text-[var(--sl-text-primary)]">End-to-End Encryption</h2>
            <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">
                Pertukaran kunci ECDH P-256 di sisi klien dan enkripsi pesan AES-GCM 256-bit di atas transport DTLS WebRTC.
            </p>
        </div>
    </section>

</div>
@endsection
