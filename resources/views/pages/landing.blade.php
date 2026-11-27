@extends('layouts.app')

@section('title', 'SecureLink — Private Peer-to-Peer Messaging')

@section('content')
<div class="py-6 sm:py-10 max-w-5xl mx-auto space-y-20">

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

        <!-- Realistic 3-Pane Application Mockup -->
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

            <div class="grid grid-cols-1 md:grid-cols-12 min-h-[290px]">
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
                            <div class="max-w-[78%] px-3 py-2 rounded-xl rounded-tr-sm" style="background-color: var(--sl-bubble-out); color: var(--sl-bubble-out-text);">
                                <div>Terverifikasi. Pesan terenkripsi AES-GCM tanpa melewati memori server.</div>
                                <div class="text-[10px] opacity-80 mt-1 text-right">09:42 &middot; ✓✓ Delivered</div>
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

    <!-- 2. FEATURES SECTION (With Two-User Conversation Simulation) -->
    <section id="features" class="space-y-8 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Features</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Fitur Komunikasi Lengkap &amp; Terintegrasi</h2>
            <p class="text-xs sm:text-sm text-[var(--sl-text-secondary)]">Dibangun di atas WebRTC DataChannel, Web Crypto API, dan Laravel Reverb Signaling.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Realistic Two-User Conversation Simulation -->
            <div class="lg:col-span-5 sl-surface rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-[var(--sl-main-surface)] border-b border-[var(--sl-divider)] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-[var(--sl-primary-teal)] text-white dark:text-[#182425] text-[10px] font-bold flex items-center justify-center">N</div>
                        <div>
                            <div class="text-xs font-bold text-[var(--sl-text-primary)]">Nadia &harr; Bagas</div>
                            <div class="text-[10px] text-[var(--sl-primary-teal)] font-medium">Direct P2P &middot; Ephemeral 30s</div>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[var(--sl-text-secondary)]">AES-GCM</span>
                </div>

                <div class="p-4 bg-[var(--sl-chat-bg)] space-y-3 text-xs">
                    <div class="flex items-start gap-2">
                        <div class="w-6 h-6 rounded-full bg-[var(--sl-secondary-teal)] text-white text-[10px] font-bold flex items-center justify-center shrink-0">N</div>
                        <div class="px-3 py-2 rounded-xl rounded-tl-sm bg-[var(--sl-bubble-in)] border border-[var(--sl-divider)] max-w-[85%]">
                            <div class="text-[var(--sl-text-primary)]">QR undangan grup sudah kubagikan, silakan scan ya.</div>
                            <div class="text-[10px] text-[var(--sl-text-secondary)] mt-1">10:14</div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="px-3 py-2 rounded-xl rounded-tr-sm max-w-[85%]" style="background-color: var(--sl-bubble-out); color: var(--sl-bubble-out-text);">
                            <div class="mb-1 px-2 py-1 rounded bg-black/5 border-l-2 border-[var(--sl-primary-teal)] text-[10px] opacity-80">Nadia: QR undangan grup sudah kubagikan...</div>
                            <div>Sudah masuk via QR! Pesan ini akan terhapus otomatis.</div>
                            <div class="text-[10px] opacity-80 mt-1 flex items-center justify-end gap-1.5">
                                <span>10:15</span>
                                <span class="text-[var(--sl-accent-coral)] font-semibold">⏱ 28s</span>
                                <span>✓✓</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 pt-1">
                        <span class="px-2 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[10px]">👍 1</span>
                        <span class="px-2 py-0.5 rounded-full bg-[var(--sl-elevated)] border border-[var(--sl-divider)] text-[10px]">🔒 1</span>
                    </div>
                </div>
            </div>

            <!-- 6 Core Capabilities -->
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">Private Chat</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Percakapan 1-lawan-1 langsung antarperangkat dengan balasan pesan, reaksi emoji, dan status ACK.</p>
                </div>
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">Group Chat</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Ruang obrolan multi-anggota berbasis topologi P2P Mesh dengan kontrol admin dan kunci room.</p>
                </div>
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">Peer-to-Peer Communication</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Transmisi pesan via WebRTC DataChannel dengan deteksi jujur jalur Direct P2P atau TURN Relay.</p>
                </div>
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">End-to-End Encryption</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Pertukaran kunci ECDH P-256 dan enkripsi AES-GCM 256-bit di browser sebelum data dikirim.</p>
                </div>
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">QR Code Invitation</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Undangan bertoken acak dengan masa berlaku, pratinjau room, kamera scanner, dan pencabutan token.</p>
                </div>
                <div class="sl-surface rounded-xl p-4 space-y-1.5">
                    <h3 class="text-xs font-bold text-[var(--sl-text-primary)]">Ephemeral Messaging</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Timer kedaluwarsa otomatis (10 detik hingga 5 menit) pada sisi pengirim dan penerima.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. HOW IT WORKS SECTION (Visual 3-Step Walkthrough) -->
    <section id="how-it-works" class="space-y-6 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">How It Works</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Alur Kerja dari Koneksi hingga Pesan P2P</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Step 01 -->
            <div class="sl-surface rounded-xl p-5 flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">01 &mdash; Open SecureLink</div>
                    <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Buka Aplikasi &amp; Sesi</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Tekan Open App untuk masuk ke workspace chat tanpa akun atau nomor telepon.</p>
                </div>
                <div class="p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] text-[11px] space-y-1.5">
                    <div class="font-semibold text-[var(--sl-text-primary)]">Sesi Identitas Lokal</div>
                    <div class="flex items-center justify-between text-[var(--sl-text-secondary)]">
                        <span>Display Name</span>
                        <span class="px-2 py-0.5 rounded bg-[var(--sl-accent-coral)] text-white text-[10px] font-semibold">Open App</span>
                    </div>
                </div>
            </div>

            <!-- Step 02 -->
            <div class="sl-surface rounded-xl p-5 flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">02 &mdash; Create or Join a Room</div>
                    <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Buat atau Pindai QR Room</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Gunakan tombol New Chat untuk membuat Private/Group room atau scan QR undangan.</p>
                </div>
                <div class="p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] text-[11px] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-[var(--sl-text-primary)]">New Chat</span>
                        <span class="font-mono text-[10px] text-[var(--sl-primary-teal)]">SEC-8F2-9K1</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 text-[10px] text-center">
                        <span class="py-1 rounded bg-[var(--sl-elevated)] border border-[var(--sl-divider)]">Private / Group</span>
                        <span class="py-1 rounded bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] font-semibold">Scan QR Code</span>
                    </div>
                </div>
            </div>

            <!-- Step 03 -->
            <div class="sl-surface rounded-xl p-5 flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="text-xs font-mono font-bold text-[var(--sl-accent-coral)]">03 &mdash; Start Secure Messaging</div>
                    <h3 class="text-sm font-bold text-[var(--sl-text-primary)]">Pertukaran Data P2P</h3>
                    <p class="text-xs text-[var(--sl-text-secondary)] leading-relaxed">Signaling SDP/ICE selesai via Reverb, lalu pesan mengalir langsung antar-peer.</p>
                </div>
                <div class="p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] text-[10px] font-mono space-y-1.5">
                    <div class="text-center text-[var(--sl-text-secondary)]">[Signaling Reverb: SDP &amp; ICE]</div>
                    <div class="flex items-center justify-between text-[var(--sl-primary-teal)] font-bold">
                        <span>[Peer A]</span>
                        <span>&harr; DTLS + AES-GCM &harr;</span>
                        <span>[Peer B]</span>
                    </div>
                </div>
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

    <!-- 5. FAQ SECTION (Interactive Accordion) -->
    <section id="faq" class="space-y-5 scroll-mt-20">
        <div class="max-w-xl space-y-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">FAQ</span>
            <h2 class="text-2xl font-bold text-[var(--sl-text-primary)]">Pertanyaan Umum</h2>
        </div>

        <div class="sl-surface rounded-xl divide-y divide-[var(--sl-divider)] overflow-hidden text-xs">
            @php
                $faqs = [
                    [
                        'q' => 'Bagaimana cara memulai percakapan di SecureLink?',
                        'a' => 'Klik Open App untuk masuk ke workspace, tentukan nama tampilan sesi, lalu tekan tombol New Chat untuk memulai Private Chat, membuat Group, atau bergabung ke room.',
                    ],
                    [
                        'q' => 'Apa perbedaan Private Chat dan Group Chat?',
                        'a' => 'Private Chat digunakan untuk komunikasi 1-lawan-1 (maksimal 2 peer), sedangkan Group Chat mendukung beberapa anggota dalam topologi P2P Mesh dengan pengaturan admin dan kunci room.',
                    ],
                    [
                        'q' => 'Bagaimana keamanan QR Code Invitation bekerja?',
                        'a' => 'QR Code hanya berisi URL undangan dengan token acak yang disimpan dalam bentuk hash di database, memiliki masa berlaku, dan dapat dicabut oleh pembuat room kapan saja. Tidak ada private key yang dimasukkan ke QR.',
                    ],
                    [
                        'q' => 'Bagaimana jika perangkat berada di balik firewall atau NAT ketat?',
                        'a' => 'WebRTC menggunakan STUN untuk penemuan alamat publik dan secara otomatis dapat beralih ke TURN Relay jika jalur langsung antarperangkat tidak memungkinkan. Indikator UI akan menampilkan status TURN Relay secara jujur.',
                    ],
                ];
            @endphp

            @foreach($faqs as $idx => $faq)
                <div class="faq-item">
                    <button type="button" onclick="toggleFaq({{ $idx }})" class="w-full px-5 py-4 flex items-center justify-between gap-4 text-left hover:bg-[var(--sl-hover)] transition">
                        <span class="font-bold text-[var(--sl-text-primary)]">{{ $faq['q'] }}</span>
                        <svg id="faq-chevron-{{ $idx }}" class="w-4 h-4 text-[var(--sl-text-secondary)] shrink-0 transition-transform duration-200 {{ $idx === 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="faq-body-{{ $idx }}" class="px-5 pb-4 text-[var(--sl-text-secondary)] leading-relaxed {{ $idx === 0 ? '' : 'hidden' }}">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>

@push('scripts')
<script>
    function toggleFaq(index) {
        const body = document.getElementById('faq-body-' + index);
        const chevron = document.getElementById('faq-chevron-' + index);
        if (!body) return;
        const isHidden = body.classList.contains('hidden');
        body.classList.toggle('hidden', !isHidden);
        if (chevron) chevron.classList.toggle('rotate-180', isHidden);
    }
</script>
@endpush
@endsection
