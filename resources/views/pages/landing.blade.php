@extends('layouts.app')

@section('title', 'SecureLink — Private Peer-to-Peer Messaging')

@section('content')
<div class="py-8 sm:py-12 max-w-5xl mx-auto space-y-16">

    <!-- 1. HERO SECTION -->
    <section id="hero" class="space-y-8 scroll-mt-20">
        <div class="text-center max-w-2xl mx-auto space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-[var(--sl-primary-teal)]"></span>
                <span>WebRTC DataChannel &middot; DTLS &middot; E2EE AES-GCM</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-[var(--sl-text-primary)] leading-[1.15]">
                Komunikasi Peer-to-Peer Privat, Langsung Antarperangkat.
            </h1>

            <p class="text-sm sm:text-base text-[var(--sl-text-secondary)] leading-relaxed">
                SecureLink menghubungkan perangkat secara langsung melalui WebRTC DataChannel dengan enkripsi ujung-ke-ujung. Server hanya membantu negosiasi sinyal koneksi tanpa pernah menyimpan isi percakapan Anda.
            </p>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="w-full sm:w-auto px-6 py-2.5 rounded-lg sl-btn-coral font-semibold text-sm inline-flex items-center justify-center gap-2">
                    Open App
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="#how-it-works"
                   class="w-full sm:w-auto px-6 py-2.5 rounded-lg sl-btn-secondary font-semibold text-sm inline-flex items-center justify-center">
                    How It Works
                </a>
            </div>
        </div>

        <!-- Realistic Chat App Preview -->
        <div class="sl-surface rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-2.5 bg-[var(--sl-main-surface)] border-b border-[var(--sl-divider)] flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#EC643E]/70"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#71999A]/70"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#497173]/70"></span>
                </div>
                <span class="text-[11px] font-mono text-[var(--sl-text-secondary)]">SecureLink Desktop Workspace</span>
                <span class="text-[11px] font-semibold text-[var(--sl-primary-teal)]">DTLS + AES-GCM</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 min-h-[280px]">
                <div class="hidden md:flex md:col-span-1 bg-[var(--sl-nav-bg)] text-white flex-col items-center py-4 gap-4">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div class="w-8 h-8 rounded-lg opacity-75 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                </div>

                <div class="md:col-span-4 border-b md:border-b-0 md:border-r border-[var(--sl-divider)] bg-[var(--sl-conv-bg)] p-3 space-y-2">
                    <div class="flex items-center justify-between px-1 pb-1">
                        <span class="text-xs font-bold text-[var(--sl-text-primary)]">Chats</span>
                        <span class="px-2 py-0.5 rounded bg-[var(--sl-accent-coral)] text-white text-[10px] font-semibold">+ New Chat</span>
                    </div>
                    <div class="px-2.5 py-2 rounded-lg bg-[var(--sl-active-conv)] flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center shrink-0">R</div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">Raka Pratama</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">Direct P2P aktif</div>
                            </div>
                        </div>
                        <span class="text-[10px] text-[var(--sl-text-secondary)]">09:42</span>
                    </div>
                    <div class="px-2.5 py-2 rounded-lg flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[var(--sl-secondary-teal)] text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center shrink-0">K3</div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-[var(--sl-text-primary)] truncate">Lab Jaringan Kelompok 3</div>
                                <div class="text-[11px] text-[var(--sl-text-secondary)] truncate">4 anggota terhubung</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-7 bg-[var(--sl-chat-bg)] flex flex-col justify-between p-4 space-y-3">
                    <div class="flex items-center justify-between pb-2.5 border-b border-[var(--sl-divider)]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] text-xs font-bold flex items-center justify-center">R</div>
                            <div>
                                <div class="text-xs font-bold text-[var(--sl-text-primary)]">Raka Pratama</div>
                                <div class="text-[11px] text-[var(--sl-primary-teal)] font-medium">P2P Direct &middot; E2EE Active</div>
                            </div>
                        </div>
                        <span class="text-[11px] px-2 py-0.5 rounded bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[var(--sl-text-secondary)]">QR Invite</span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-start">
                            <div class="max-w-[78%] px-3 py-2 rounded-xl rounded-tl-sm bg-[var(--sl-bubble-in)] border border-[var(--sl-divider)] text-[var(--sl-text-primary)]">
                                <div>ICE candidate selesai, DataChannel sudah terbuka langsung antarperangkat.</div>
                                <div class="text-[10px] text-[var(--sl-text-secondary)] mt-1 text-right">09:41</div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <div class="max-w-[78%] px-3 py-2 rounded-xl rounded-tr-sm bg-[var(--sl-bubble-out)] text-[var(--sl-text-primary)]">
                                <div>Terverifikasi. Pesan terenkripsi AES-GCM tanpa melewati memori server.</div>
                                <div class="text-[10px] text-[var(--sl-primary-teal)] mt-1 text-right">09:42 &middot; Delivered</div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[var(--sl-divider)] flex items-center gap-2">
                        <div class="flex-1 px-3 py-1.5 rounded-lg bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-xs text-[var(--sl-text-secondary)]">
                            Tulis pesan terenkripsi...
                        </div>
                        <div class="px-3 py-1.5 rounded-lg bg-[var(--sl-accent-coral)] text-white text-xs font-semibold">Kirim</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FEATURES SECTION -->
    <section id="features" class="space-y-6 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Features</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Dirancang untuk Komunikasi Privat &amp; Terkontrol</h2>
            <p class="text-xs sm:text-sm text-[var(--sl-text-secondary)]">Seluruh fitur dibangun langsung di atas arsitektur WebRTC DataChannel dan Web Crypto API.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Private Chat</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Percakapan 1-lawan-1 langsung antarperangkat dengan status pengiriman dan balasan pesan.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Group Chat</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Ruang obrolan multi-anggota berbasis topologi P2P Mesh dengan kontrol admin dan kunci room.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Peer-to-Peer Communication</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Transmisi pesan melalui WebRTC DataChannel dengan deteksi jalur Direct P2P atau TURN Relay.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">End-to-End Encryption</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Pertukaran kunci ECDH P-256 dan enkripsi AES-GCM 256-bit di browser sebelum pesan dikirim.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">QR Code Invitation</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Bagikan undangan bertoken acak melalui QR Code dengan masa berlaku dan fitur pencabutan token.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Ephemeral Messaging</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Atur durasi kedaluwarsa pesan (10 detik hingga 5 menit) agar pesan terhapus otomatis.</p>
            </div>
        </div>
    </section>

    <!-- 3. HOW IT WORKS SECTION -->
    <section id="how-it-works" class="space-y-6 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">How It Works</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Tiga Langkah Memulai Komunikasi</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">01 &mdash; Open SecureLink</div>
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Buka Aplikasi &amp; Siapkan Sesi</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Tekan Open App untuk masuk ke workspace chat tanpa registrasi akun atau nomor telepon.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">02 &mdash; Create or Join a Room</div>
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Buat atau Gabung Room</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Pilih New Chat untuk membuat Private Chat atau Group, lalu bagikan QR Code atau Room ID.</p>
            </div>
            <div class="sl-surface rounded-xl p-5 space-y-2">
                <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">03 &mdash; Start Secure Messaging</div>
                <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Kirim Pesan Terenkripsi</h3>
                <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Setelah signaling SDP &amp; ICE selesai, pesan mengalir langsung antarperangkat melalui DataChannel.</p>
            </div>
        </div>
    </section>

    <!-- 4. SECURITY SECTION -->
    <section id="security" class="sl-surface rounded-xl p-6 sm:p-8 space-y-5 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Security</span>
            <h2 class="text-xl font-bold text-[var(--sl-text-primary)]">Arsitektur Keamanan Transparan</h2>
            <p class="text-xs sm:text-sm text-[var(--sl-text-secondary)]">SecureLink menampilkan status koneksi dan kriptografi secara jujur sesuai kondisi jaringan aktual.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">WebRTC &amp; DTLS Transport</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Setiap kanal DataChannel menggunakan enkripsi transport DTLS sebagai standar protokol WebRTC.</p>
            </div>
            <div class="p-4 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">End-to-End Encryption</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Kunci bersama diturunkan secara lokal dengan ECDH P-256 untuk mengenkripsi payload via AES-GCM.</p>
            </div>
            <div class="p-4 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Direct P2P dan TURN Relay</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Sistem memeriksa kandidat ICE aktif agar UI membedakan jalur Direct P2P dengan TURN Relay secara akurat.</p>
            </div>
            <div class="p-4 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Perlindungan Komunikasi</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Pada mode P2P, server Laravel Reverb hanya merutekan sinyal negosiasi dan tidak menyimpan plain-text chat.</p>
            </div>
        </div>
    </section>

    <!-- 5. FAQ SECTION -->
    <section id="faq" class="space-y-5 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">FAQ</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Pertanyaan Umum</h2>
        </div>

        <div class="divide-y divide-[var(--sl-divider)] sl-surface rounded-xl overflow-hidden text-xs">
            <div class="p-4 sm:p-5 space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Bagaimana cara menggunakan SecureLink?</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Klik Open App, tentukan nama tampilan sesi, lalu gunakan tombol New Chat untuk memulai Private Chat, membuat Group, atau bergabung ke room.</p>
            </div>
            <div class="p-4 sm:p-5 space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Apa perbedaan Private Chat dan Group Chat?</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Private Chat ditujukan untuk komunikasi 1-lawan-1 (maksimal 2 peer), sedangkan Group Chat mendukung banyak peserta dalam topologi P2P Mesh dengan pengaturan admin.</p>
            </div>
            <div class="p-4 sm:p-5 space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Bagaimana cara kerja QR Code Invitation?</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">Pemilik atau anggota room dapat membuat QR Code berisi tautan undangan bertoken acak yang memiliki masa berlaku dan dapat dicabut kapan saja.</p>
            </div>
            <div class="p-4 sm:p-5 space-y-1">
                <div class="font-bold text-[var(--sl-text-primary)]">Bagaimana jika perangkat berada di balik firewall atau NAT ketat?</div>
                <p class="text-[var(--sl-text-secondary)] leading-relaxed">WebRTC menggunakan STUN untuk penemuan alamat publik dan dapat beralih ke TURN Relay jika koneksi langsung antarperangkat tidak memungkinkan.</p>
            </div>
        </div>
    </section>

</div>
@endsection
