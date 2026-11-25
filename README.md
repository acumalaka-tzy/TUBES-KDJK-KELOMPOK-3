# PRODUCT REQUIREMENTS DOCUMENT (PRD)

# SecureLink --- Secure Peer-to-Peer Messaging Platform

**Versi:** 2.1\
**Status:** Product Specification\
**Jenis Produk:** Web Application\
**Kategori:** Computer Networks and Data Communication\
**Bahasa Antarmuka:** Bahasa Indonesia (dengan istilah teknis berbahasa
Inggris)\
**Platform:** Desktop, tablet, dan mobile browser

------------------------------------------------------------------------

## Riwayat Dokumen

  -----------------------------------------------------------------------
  Versi                               Perubahan
  ----------------------------------- -----------------------------------
  1.1                                 Rancangan awal aplikasi chatting
                                      P2P dengan WebRTC, VPN, DTLS, E2EE,
                                      dan demonstrasi Broadcast.

  2.0                                 Penyempurnaan spesifikasi,
                                      penambahan Room Administration,
                                      Reliable Message Delivery, Message
                                      Interaction, QR Invitation, serta
                                      pembaruan arah desain UI/UX.
  -----------------------------------------------------------------------

# 1. PRODUCT OVERVIEW

## 1.1 Deskripsi Produk

SecureLink adalah aplikasi chatting berbasis web yang dirancang untuk
mendemonstrasikan komunikasi data yang lebih privat melalui jaringan
Peer-to-Peer (P2P). Aplikasi menggunakan WebRTC DataChannel untuk
pertukaran pesan secara langsung antarperangkat setelah proses signaling
dan negosiasi koneksi selesai.

WebSocket digunakan sebagai signaling channel untuk membantu pertukaran
SDP Offer/Answer dan ICE Candidate. Signaling server tidak meneruskan
isi pesan pada mode P2P setelah DataChannel terbentuk. Jika koneksi
langsung tidak memungkinkan, WebRTC dapat menggunakan TURN relay;
kondisi ini harus ditampilkan secara jujur kepada pengguna.

Keamanan komunikasi menggunakan beberapa lapisan dengan fungsi yang
berbeda: - **VPN Tailscale:** menyediakan jaringan privat antarperangkat
yang dikonfigurasi pada lingkungan pengujian. - **DTLS:** mengamankan
transport WebRTC DataChannel. - **End-to-End Encryption (E2EE):**
mengenkripsi isi pesan pada perangkat pengirim dan mendekripsinya pada
perangkat penerima. - **Room PIN:** membatasi akses ke ruang obrolan. -
**Ephemeral Messaging:** menyediakan pesan yang kedaluwarsa dan dihapus
dari tampilan serta memori aplikasi sesuai mekanisme yang ditetapkan.

SecureLink juga menyediakan Broadcast Comparison Mode sebagai pembanding
komunikasi server-based dengan komunikasi P2P. Hal ini menjadikan
aplikasi tidak hanya sebagai media chatting, tetapi juga media
demonstrasi konsep jaringan komputer dan keamanan komunikasi.

## 1.2 Problem Statement

Komunikasi melalui jaringan internet memiliki sejumlah risiko dan
tantangan: - Data dapat terekspos apabila dikirim tanpa perlindungan
enkripsi yang memadai. - Server perantara dapat menjadi titik
ketergantungan dan titik konsentrasi data. - Pengguna sering tidak
mengetahui apakah koneksi berjalan secara langsung atau melalui relay. -
Koneksi antarperangkat dapat gagal karena NAT, firewall, atau
konfigurasi jaringan. - Pengiriman pesan dapat mengalami kegagalan
ketika koneksi terputus. - Pengguna membutuhkan cara sederhana untuk
mengelola akses dan anggota ruang obrolan.

SecureLink dikembangkan untuk mendemonstrasikan bagaimana signaling,
P2P, VPN, DTLS, dan E2EE bekerja, sekaligus menyediakan pengalaman
chatting yang mudah dipahami.

## 1.3 Product Goals

1.  Mengimplementasikan komunikasi real-time antarperangkat menggunakan
    WebRTC DataChannel.
2.  Menggunakan WebSocket sebagai signaling channel.
3.  Menggunakan Tailscale sebagai dukungan jaringan privat pada skenario
    pengujian.
4.  Menerapkan DTLS pada transport WebRTC dan E2EE untuk isi pesan.
5.  Menyediakan perbandingan komunikasi server-based dan P2P.
6.  Menyediakan pengelolaan room dan anggota.
7.  Mengurangi kegagalan pengiriman pesan melalui acknowledgment dan
    retry yang terkontrol.
8.  Menyediakan pesan sementara dan perlindungan akses room menggunakan
    PIN.
9.  Menyediakan informasi koneksi dan keamanan yang berdasarkan kondisi
    aktual.
10. Menghadirkan UI modern, berkarakter, responsif, dan nyaman digunakan
    dalam Light Mode maupun Dark Mode.

## 1.4 Product Principles

-   **Privacy by design:** isi pesan tidak disimpan permanen oleh server
    pada mode P2P.
-   **Honest security indicators:** aplikasi hanya menampilkan status
    keamanan yang benar-benar dapat diverifikasi.
-   **Chat first:** pengalaman chatting menjadi fokus utama, sedangkan
    informasi teknis ditempatkan di Settings atau halaman Demonstration.
-   **Understandable controls:** pengaturan keamanan harus memiliki
    penjelasan yang mudah dipahami.
-   **Resilient communication:** status pesan dan koneksi harus jelas
    ketika jaringan tidak stabil.
-   **Visual restraint:** desain mengutamakan hierarki, tipografi, ruang
    kosong, dan detail interaksi; bukan dekorasi berlebihan.

# 2. TARGET USERS & ROLES

## 2.1 Target Users

Mahasiswa, pengajar, pengembang, atau pengguna yang ingin mempelajari
komunikasi jaringan, WebRTC, P2P, VPN, dan konsep keamanan komunikasi
melalui aplikasi web yang dapat diuji pada lebih dari satu perangkat.

## 2.2 User Roles

### Room Creator

Pengguna yang membuat dan mengelola room.

Hak akses: - Membuat dan mengatur room. - Mengatur PIN dan konfigurasi
keamanan room. - Mengundang peserta. - Melihat daftar peserta. -
Mengeluarkan peserta dari room. - Mengunci atau membuka room. - Mengatur
izin peserta. - Mengakhiri room atau menyerahkan kepemilikan room jika
fitur transfer ownership diaktifkan.

### Participant

Pengguna yang bergabung ke room.

Hak akses: - Mengirim dan menerima pesan. - Melihat daftar peserta. -
Menggunakan fitur interaksi pesan. - Menyalin atau membagikan undangan
sesuai izin. - Keluar dari room. - Melihat status koneksi dan keamanan.

### Identity Model

Versi awal menggunakan Display Name dan User ID unik untuk identifikasi
dalam sesi. Model ini bukan autentikasi akun yang kuat. PRD tidak boleh
menyebut identitas tersebut sebagai bukti identitas dunia nyata. Akses
room dan operasi administratif tetap harus divalidasi oleh backend
berdasarkan sesi dan membership yang sah.

# 3. CORE FEATURES

## 3.1 User Identity

Fitur untuk mengidentifikasi pengguna di dalam aplikasi.

Subfitur: - Input Display Name. - Pembuatan User ID unik. - Pengubahan
Display Name. - Penyimpanan preferensi identitas secara lokal. - Status
online, offline, dan reconnecting. - Validasi nama pengguna dan batas
panjang input.

Catatan keamanan: - Display Name tidak dianggap sebagai bukti
autentikasi. - User ID tidak boleh menjadi satu-satunya dasar
otorisasi. - Operasi sensitif harus memvalidasi sesi dan hak akses pada
server.

## 3.2 Chat Room Management

Fitur untuk membuat dan mengelola ruang komunikasi.

Subfitur: - Create Room. - Join Room menggunakan Room ID. - Join Room
menggunakan Room ID dan PIN jika diwajibkan. - Generate Room ID unik. -
Copy Room ID. - Room Name. - Room Description opsional. - Daftar anggota
room. - Informasi pembuat room. - Status online/offline peserta. - Batas
maksimum peserta. - Leave Room. - Room expiration dan pembersihan
metadata room kedaluwarsa. - Validasi membership pada setiap operasi
signaling.

## 3.3 Real-Time Messaging

Fitur utama untuk mengirim dan menerima pesan.

Subfitur: - Mengirim dan menerima pesan teks secara real-time. -
Identitas pengirim dan timestamp. - Message ID unik. - Status pesan. -
Indikator pesan diterima. - Penanganan pesan gagal dikirim. - Notifikasi
pesan baru. - Auto-scroll dengan perilaku yang tidak memaksa ketika
pengguna membaca pesan lama. - System Message untuk aktivitas room. -
Connection Message untuk perubahan koneksi. - Validasi ukuran dan format
payload.

Jenis pesan: - Text Message. - System Message. - Connection Message.

## 3.4 WebRTC Peer-to-Peer Communication

WebRTC digunakan untuk membangun koneksi data antarperangkat.

Subfitur: - RTCPeerConnection. - RTCDataChannel. - SDP Offer dan
Answer. - ICE Candidate Exchange. - ICE Connection State Monitoring. -
Peer Connection State Monitoring. - DataChannel State Monitoring. -
Disconnect Handling. - Reconnection dengan batas percobaan dan jeda
bertahap. - Penanganan koneksi P2P langsung dan TURN relay.

Status koneksi: - Disconnected. - Connecting. - Connected. -
Reconnecting. - Failed.

Ketentuan: - Aplikasi tidak boleh menganggap koneksi P2P langsung hanya
karena DataChannel aktif. - Jenis jalur koneksi ditentukan berdasarkan
selected ICE candidate pair. - Jika selected candidate pair menunjukkan
relay, UI harus menampilkan Relay, bukan Direct P2P. - STUN digunakan
untuk membantu NAT traversal dan TURN sebagai fallback relay.

## 3.5 Signaling Server

Signaling server membantu proses pembentukan koneksi WebRTC.

Subfitur: - WebSocket Secure (WSS) pada deployment HTTPS. - Register
User Session. - Register Room. - Room Membership Management. -
Send/Receive SDP Offer. - Send/Receive SDP Answer. - Exchange ICE
Candidate. - User Join/Leave Notification. - Connection Status
Notification. - Validasi format dan ukuran payload. - Rate limiting
untuk event signaling. - Validasi bahwa pengirim dan penerima merupakan
anggota room yang sah.

Ketentuan: - Signaling server membantu pembentukan koneksi. - Pada mode
P2P, server tidak meneruskan isi pesan setelah DataChannel terbentuk. -
Server tidak menyimpan plain-text chat. - Data signaling harus dibatasi
pada kebutuhan negosiasi koneksi. - Log tidak boleh menyimpan private
key, shared secret, PIN, atau isi pesan.

## 3.6 VPN Integration --- Tailscale

Tailscale digunakan sebagai dukungan jaringan privat untuk skenario
demonstrasi dan pengujian.

Subfitur: - Panduan instalasi Tailscale. - Panduan login perangkat. -
Panduan menghubungkan perangkat ke Tailnet yang sama. - Informasi alamat
IP Tailscale yang perlu dikonfigurasi oleh operator. - Panduan mengakses
signaling server melalui jaringan privat. - Pemeriksaan konektivitas ke
signaling server. - Troubleshooting koneksi.

Catatan: Browser tidak dapat mengetahui status aplikasi Tailscale secara
langsung tanpa integrasi tambahan. UI hanya boleh menampilkan hasil
pemeriksaan konektivitas yang benar-benar dilakukan, bukan mengklaim
status VPN aktif berdasarkan asumsi.

## 3.7 Security Information

Informasi keamanan tersedia melalui Settings dan indikator ringkas pada
halaman chat.

Informasi: - Connection Type: Direct / Relay. - WebRTC Connection
State. - ICE Connection State. - DataChannel State. - DTLS Transport
State. - E2EE Status. - Status signaling server. - Jumlah peer
terhubung. - Panduan VPN.

Indikator ringkas pada chat: - Secure Connection (hanya jika kondisi
yang disyaratkan terpenuhi). - Connecting. - Reconnecting. -
Disconnected.

Status DTLS dan E2EE harus dibedakan. DTLS aktif tidak otomatis berarti
E2EE aplikasi sudah aktif.

## 3.8 Security Log

Mencatat aktivitas teknis dan keamanan tanpa menyimpan rahasia.

Event yang dapat dicatat: - User Joined/Left Room. - Peer Connection
Started. - SDP Offer/Answer Exchanged. - ICE Candidate Exchanged. -
WebRTC Connected/Failed. - DTLS Connected/Failed. - E2EE
Initialized/Failed. - Message Delivery Acknowledged. - Connection
Reconnected.

Log tidak boleh mencatat: - Plain-text message. - Private key atau
shared secret. - PIN. - Token sesi. - Payload rahasia.

# 4. ADVANCED SECURITY FEATURES

## 4.1 End-to-End Encryption (E2EE)

E2EE merupakan fitur inti SecureLink dan dipertahankan dari PRD
sebelumnya. Pesan dienkripsi pada perangkat pengirim sebelum dikirim dan
hanya didekripsi pada perangkat penerima yang memiliki material kunci
yang sesuai.

Komponen: - Generate Public/Private Key Pair. - Public Key Exchange
melalui signaling. - Shared Secret Derivation. - AES-GCM Encryption. -
Message Decryption. - Authentication Tag Verification. - Nonce/IV
Management. - Key Lifecycle Management. - Encryption/Decryption Error
Handling.

Alur konseptual: 1. Setiap peer membuat pasangan kunci pada
perangkatnya. 2. Public key dipertukarkan melalui signaling. 3. Kedua
peer menurunkan shared secret menggunakan mekanisme pertukaran kunci
yang disepakati. 4. Key Derivation Function digunakan untuk menghasilkan
kunci enkripsi simetris. 5. Pengirim mengenkripsi pesan menggunakan
AES-GCM dengan IV unik. 6. Ciphertext dan metadata minimum dikirim
melalui DataChannel. 7. Penerima memverifikasi authentication tag dan
mendekripsi pesan. 8. Pesan ditampilkan setelah dekripsi berhasil.

Spesifikasi keamanan yang harus dipenuhi: - Gunakan algoritma pertukaran
kunci yang sesuai, misalnya ECDH melalui Web Crypto API. - Gunakan HKDF
untuk derivasi kunci dari shared secret. - Gunakan AES-GCM untuk
enkripsi dan autentikasi pesan. - IV/nonce harus unik untuk setiap
operasi enkripsi dengan kunci yang sama. - Private key dan shared secret
tidak boleh dikirim ke server. - Private key tidak boleh disimpan dalam
bentuk plain-text. - Kegagalan autentikasi tag harus menyebabkan pesan
ditolak, bukan ditampilkan sebagian. - Kunci harus memiliki lifecycle
yang jelas, termasuk pembuatan ulang ketika sesi atau konteks keamanan
berubah. - Pertukaran public key melalui signaling belum otomatis
membuktikan identitas peer. Versi ini tidak menjadikan verifikasi
fingerprint sebagai fitur produk tersendiri; keterbatasan tersebut harus
dicantumkan dalam dokumentasi keamanan.

## 4.2 Ephemeral Messaging

Fitur pesan sementara yang kedaluwarsa secara otomatis.

Pilihan durasi: - 10 detik. - 30 detik. - 60 detik. - 5 menit.

Subfitur: - Mengaktifkan pesan sementara. - Memilih durasi. - Countdown
pada pesan. - Sinkronisasi waktu kedaluwarsa antarperangkat. - Menghapus
pesan dari tampilan dan memori aplikasi setelah kedaluwarsa. - System
notification ketika pesan kedaluwarsa.

Ketentuan: - Waktu kedaluwarsa dikirim sebagai metadata pesan. - Kedua
sisi menggunakan timestamp kedaluwarsa yang sama. - Pesan tidak disimpan
permanen di database. - Penghapusan dari aplikasi tidak menjamin
penerima tidak sempat menyalin, merekam, atau mengambil screenshot. -
Jangan menjanjikan penghapusan forensik dari seluruh sistem operasi atau
perangkat.

## 4.3 Dynamic PIN Protected Room

Fitur perlindungan room menggunakan PIN.

Subfitur: - Set Room PIN. - Join Room dengan PIN. - Validasi PIN
server-side. - Pembatasan percobaan PIN. - Cooldown setelah percobaan
gagal. - PIN expiration. - Dynamic PIN menggunakan TOTP jika
diaktifkan. - Notifikasi perubahan PIN.

Ketentuan: - PIN tidak boleh disimpan sebagai plain-text. - Gunakan hash
yang sesuai untuk PIN statis dan mekanisme verifikasi yang aman. -
Secret TOTP harus disimpan dan dikelola secara aman. - Validasi akses
selalu dilakukan server-side. - Jangan mengirim secret TOTP kepada
peserta room.

## 4.4 Room Access Protection

-   Room dapat dikunci oleh Room Creator.
-   Join request baru ditolak ketika room terkunci, kecuali mekanisme
    persetujuan secara eksplisit diaktifkan.
-   Peserta yang sudah berada di room tetap dapat berkomunikasi sesuai
    kebijakan room.
-   Perubahan akses harus diberitahukan melalui System Message.
-   Operasi administratif harus memeriksa role dan membership di
    backend.

# 5. ROOM ADMINISTRATION

Fitur pengelolaan peserta dan kontrol room untuk Room Creator.

## 5.1 Participant Management

Subfitur: - Melihat daftar anggota. - Melihat status koneksi anggota. -
Kick Participant. - Lock Room dan Unlock Room. - Mengatur izin
peserta. - Menampilkan notifikasi ketika peserta dikeluarkan. - Mencegah
peserta yang dikeluarkan mengirim signaling atau bergabung kembali tanpa
akses yang sah.

## 5.2 Room Permissions

Izin yang dapat diatur: - Allow Participants to Invite. - Allow
Participants to Use Message Reactions. - Allow Participants to Reply to
Messages. - Restrict New Participants. - Lock Room.

Pengaturan harus sederhana dan tidak membebani pengguna dengan terlalu
banyak konfigurasi.

## 5.3 Ownership

Versi awal: - Room Creator menjadi pemilik room. - Room Creator dapat
mengakhiri room. - Transfer Ownership dapat disiapkan sebagai
pengembangan opsional jika waktu implementasi memungkinkan.

# 6. RELIABLE MESSAGE DELIVERY

Fitur untuk memperjelas status pengiriman dan menangani kegagalan
komunikasi.

## 6.1 Message Status

Status pesan: - **Sending:** pesan sedang diproses untuk dikirim. -
**Sent:** pesan berhasil diserahkan ke transport DataChannel. -
**Delivered:** perangkat penerima telah mengirim acknowledgment. -
**Failed:** pengiriman gagal atau tidak dapat dipastikan setelah batas
waktu. - **Expired:** pesan sementara telah melewati masa berlaku.

Status harus digunakan secara konsisten. Sent tidak boleh disamakan
dengan Delivered.

## 6.2 Acknowledgment

-   Setiap pesan memiliki Message ID unik.
-   Penerima mengirim acknowledgment setelah pesan diterima dan diproses
    sesuai protokol.
-   Pengirim memperbarui status menjadi Delivered setelah acknowledgment
    diterima.
-   Pesan acknowledgment tidak boleh memuat isi pesan.
-   Sistem harus mengabaikan acknowledgment duplikat.

## 6.3 Retry Handling

-   Retry hanya dilakukan untuk pesan yang statusnya belum Delivered.
-   Retry memiliki batas percobaan.
-   Gunakan jeda bertahap (backoff).
-   Hindari pengiriman ganda dengan Message ID dan deduplication pada
    penerima.
-   Jika sesi berakhir, pesan yang belum terkirim ditandai Failed dan
    tidak dijanjikan akan tersinkronisasi lintas perangkat.
-   Untuk pesan ephemeral, retry tidak boleh melewati waktu kedaluwarsa.

## 6.4 Delivery Limitations

Karena SecureLink tidak menyediakan penyimpanan pesan permanen, reliable
delivery hanya berlaku selama konteks sesi dan koneksi yang masih
tersedia. Fitur ini bukan jaminan offline message delivery.

# 7. MESSAGE INTERACTION

Fitur interaksi untuk meningkatkan kenyamanan chatting tanpa mengubah
fokus keamanan aplikasi.

Subfitur: - Reply to Message. - Emoji Reaction. - Copy Message. - Delete
for Me. - Delete for Everyone jika didukung oleh protokol dan kebijakan
room. - Menampilkan pesan yang sedang dibalas. - Menampilkan reaction
secara ringkas. - Menampilkan system event untuk perubahan pesan yang
relevan.

Ketentuan: - Interaksi pesan harus membawa Message ID, bukan bergantung
pada posisi pesan dalam daftar. - Reaction dan reply mengikuti aturan
E2EE yang berlaku. - Delete for Everyone hanya dapat menghapus pesan
dari tampilan dan memori aplikasi penerima yang sedang terhubung; tidak
menjamin penghapusan salinan eksternal. - Menu interaksi tidak boleh
memenuhi bubble pesan. Gunakan contextual menu yang muncul saat pesan
dipilih atau diarahkan.

# 8. QR CODE ROOM INVITATION

Fitur untuk memudahkan peserta bergabung ke room.

Subfitur: - Generate QR Code dari informasi undangan. - Copy Invite
Link. - Download atau tampilkan QR Code. - Scan QR Code melalui kamera
perangkat jika browser memberikan izin. - Fallback berupa input Room ID
manual. - PIN tetap diminta secara terpisah jika room dilindungi PIN.

Ketentuan keamanan: - QR Code tidak boleh memuat private key atau shared
secret. - Invite token harus memiliki masa berlaku dan dapat dicabut
jika menggunakan token undangan. - Hindari menaruh PIN room secara
permanen di URL atau QR Code. - Validasi membership dan akses tetap
dilakukan server-side.

# 9. COMMUNICATION MODES

## 9.1 Server-Based Broadcast Mode

Mode pembanding untuk memperlihatkan komunikasi melalui server.

Alur: 1. Pengguna A dan B terhubung ke server. 2. Pengguna A mengirim
pesan ke server. 3. Server menerima pesan. 4. Server meneruskan pesan ke
pengguna B. 5. Pengguna B menerima pesan.

Karakteristik: - Server menjadi perantara pesan. - Tanpa E2EE, server
dapat memproses isi pesan. - Ketergantungan terhadap server lebih
tinggi. - Lebih mudah untuk demonstrasi distribusi pesan terpusat.

## 9.2 Peer-to-Peer WebRTC Mode

Alur: 1. Pengguna masuk ke room yang sama. 2. Signaling server menukar
informasi negosiasi. 3. WebRTC menjalankan ICE negotiation. 4. Koneksi
DataChannel terbentuk. 5. Pesan dienkripsi menggunakan E2EE jika
diaktifkan. 6. Ciphertext dikirim melalui DataChannel. 7. Penerima
memverifikasi dan mendekripsi pesan.

Karakteristik: - Signaling server tidak meneruskan isi pesan setelah
DataChannel terbentuk. - DTLS mengamankan transport WebRTC. - E2EE
menjadi lapisan enkripsi isi pesan. - Koneksi dapat menggunakan jalur
langsung atau TURN relay.

## 9.3 Comparison Mode

  -----------------------------------------------------------------------
  Aspek                   Broadcast               P2P WebRTC
  ----------------------- ----------------------- -----------------------
  Jalur pesan             Melalui server          DataChannel antar-peer
                                                  atau TURN relay

  Signaling               Tidak selalu diperlukan Diperlukan

  Server meneruskan isi   Ya                      Tidak pada mode P2P
  pesan                                           

  DTLS                    Bergantung transport    Digunakan WebRTC

  E2EE                    Dapat ditambahkan       Dapat ditambahkan

  Ketergantungan server   Lebih tinggi            Signaling tetap
                                                  diperlukan

  Koneksi relay           Tidak relevan pada pola Dapat terjadi melalui
                          broadcast               TURN
  -----------------------------------------------------------------------

# 10. APPLICATION PAGES & UI REQUIREMENTS

## 10.1 Design Direction

SecureLink menggunakan pendekatan **Modern Minimal Messaging** dengan karakter editorial:
antarmuka terasa matang, ringan, dan terstruktur seperti produk digital
yang dirancang dengan sengaja. Desain tidak menggunakan tampilan
dashboard generik yang dipenuhi kartu, gradient, glow, atau panel
statistik yang tidak diperlukan.

Karakter visual: - Clean, confident, and calm. - Memiliki identitas
warna yang kuat tetapi tidak mencolok. - Mengutamakan tipografi,
alignment, whitespace, dan detail komponen. - Menggunakan border tipis
dan elevasi secukupnya. - Gradient hanya digunakan secara terbatas untuk
elemen branding atau aksen tertentu. - Tidak menggunakan glassmorphism
berlebihan. - Tidak menampilkan informasi teknis sebagai dekorasi. -
Ikon konsisten dan memiliki fungsi yang jelas.

Inspirasi pola interaksi: aplikasi messaging desktop modern dengan
sidebar percakapan, area chat yang lapang, contextual menu, dan settings
yang tertata. Inspirasi bukan untuk menyalin tampilan produk tertentu.

### 10.1.1 UI/UX Reference and Product Direction

SecureLink mengadopsi pola interaksi aplikasi messaging modern seperti Telegram dan WhatsApp Desktop sebagai referensi usability, bukan untuk menyalin identitas visual atau tampilan keduanya secara langsung. SecureLink harus memiliki identitas visual sendiri dengan pendekatan **Modern Minimal Messaging**.

Prinsip utama:
- **Chat-first:** halaman percakapan menjadi pusat pengalaman pengguna.
- **Familiar interaction:** gunakan pola conversation list, chat header, message bubble, composer di bagian bawah, pencarian percakapan, dan contextual menu.
- **Visual restraint:** hindari dashboard generik, dekorasi berlebihan, gradient besar, glow, glassmorphism, dan penggunaan card untuk setiap elemen.
- **Clear hierarchy:** utamakan tipografi, alignment, whitespace, ukuran komponen, dan pemisahan area yang konsisten.
- **Functional decoration:** setiap ikon, badge, warna, dan animasi harus memiliki fungsi yang jelas.
- **Consistent behavior:** komponen dengan fungsi sama harus memiliki perilaku dan tampilan konsisten.

Referensi pola interaksi:
- **Telegram Desktop:** referensi conversation sidebar, daftar percakapan, navigasi, pencarian, dan struktur area chat.
- **WhatsApp Desktop:** referensi message bubble, composer, timestamp, status pesan, dan interaksi percakapan.
- Referensi hanya menjadi acuan usability. Logo, aset, ilustrasi, dan detail visual khas produk lain tidak boleh disalin.

### 10.1.2 Chat Layout Requirements

Layout utama desktop:
- Gunakan conversation sidebar di sisi kiri dan active chat area di sisi kanan.
- Sidebar memuat identitas aplikasi, pencarian percakapan, daftar room/percakapan, serta akses ke profil dan Settings.
- Active chat area memuat chat header, message history, dan message composer.
- Lebar sidebar konsisten dan dapat disesuaikan jika diperlukan; area percakapan menggunakan sisa ruang.
- Chat header menampilkan nama room, jumlah peserta atau status anggota, indikator koneksi ringkas, serta akses ke Room Information dan More Options.
- Message composer mudah dijangkau di bagian bawah area chat.
- Hindari panel statistik atau dashboard di halaman chat utama.

Layout mobile:
- Gunakan navigasi bertahap: daftar percakapan dan halaman chat menjadi tampilan terpisah.
- Pengguna dapat kembali dari percakapan ke daftar room melalui navigasi yang jelas.
- Composer harus tetap terlihat dan tidak tertutup keyboard virtual.
- Menu room, informasi koneksi, dan pengaturan ditampilkan melalui halaman, drawer, atau bottom sheet yang sesuai.
- Jangan sekadar mengecilkan layout desktop menjadi ukuran mobile.

### 10.1.3 Conversation List Requirements

- Tampilkan avatar atau inisial room, nama room, cuplikan pesan terakhir jika tersedia, dan waktu aktivitas.
- Indikator unread hanya ditampilkan jika terdapat pesan belum dibaca yang benar-benar diketahui aplikasi.
- Bedakan room aktif melalui highlight halus, bukan warna mencolok.
- Sediakan pencarian berdasarkan nama room atau informasi yang tersedia pada perangkat.
- Empty state menjelaskan cara membuat atau bergabung ke room.
- Karena SecureLink tidak menyimpan riwayat pesan permanen, cuplikan pesan terakhir dan daftar room tidak boleh diklaim tersinkronisasi lintas perangkat atau tersedia selamanya.

### 10.1.4 Message and Composer Requirements

- Pesan sendiri dan pesan peserta lain dibedakan melalui alignment dan warna bubble yang nyaman dibaca.
- Bubble tidak boleh terlalu lebar; pertahankan panjang baris yang nyaman.
- Nama pengirim, timestamp, delivery status, reply preview, dan ephemeral countdown ditampilkan secara ringkas sesuai kebutuhan.
- System Message dan Connection Message memiliki gaya berbeda dari pesan biasa, tetapi tidak mendominasi area percakapan.
- Aksi Reply, React, Copy, dan Delete ditempatkan pada contextual menu yang muncul ketika pesan dipilih atau diarahkan.
- Message composer menggunakan satu area input yang jelas, tombol kirim yang mudah ditemukan, dan kontrol tambahan yang tidak memenuhi ruang.
- Enter untuk mengirim dan Shift + Enter untuk baris baru mengikuti preferensi pengguna.
- Tampilkan state Sending, Sent, Delivered, Failed, dan Expired secara konsisten.

### 10.1.5 Anti-Patterns and UI Quality Constraints

Implementasi UI tidak boleh:
- Mengubah setiap bagian halaman menjadi card yang terpisah.
- Menggunakan gradient, glow, glassmorphism, atau shadow tebal sebagai dekorasi utama.
- Menampilkan terlalu banyak badge, statistik, panel status, atau label teknis di halaman chat.
- Menggunakan banyak warna aksen sekaligus tanpa fungsi yang jelas.
- Menampilkan seluruh kontrol pesan secara permanen di setiap bubble.
- Menggunakan placeholder, data dummy, atau status koneksi palsu seolah-olah merupakan data aktual.
- Menggunakan animasi yang mengganggu pembacaan pesan atau memperlambat interaksi.
- Menyalin tampilan, logo, atau aset Telegram maupun WhatsApp.

### 10.1.6 UI Implementation and Acceptance

- Seluruh halaman menggunakan design tokens yang ditetapkan pada Color System, Typography, dan Layout.
- Komponen utama yang wajib konsisten meliputi sidebar, conversation item, chat header, message bubble, composer, contextual menu, dialog, toast, dan connection indicator.
- Buat dan sempurnakan Chat Room sebagai prioritas visual sebelum menyelesaikan halaman sekunder.
- Setiap halaman memiliki state normal, loading, empty, error, dan disabled jika relevan.
- UI diuji pada desktop, tablet, dan mobile serta Light Mode dan Dark Mode.
- UI memenuhi arah desain apabila pengguna dapat menemukan room, membaca percakapan, mengirim pesan, melihat status pesan, dan membuka informasi room tanpa kebingungan.
- Informasi teknis seperti ICE candidate, DTLS, detail E2EE, dan VPN guidance ditempatkan di Settings, Diagnostics, atau Room Information; halaman chat hanya menampilkan indikator ringkas yang relevan.

## 10.2 Color System

### Light Mode

Light Mode menjadi tampilan terang yang hangat dan nyaman, bukan putih
polos di seluruh area.

  Token            Warna       Penggunaan
  ---------------- ----------- -------------------------------
  App Background   `#F4F6FA`   Latar utama
  Sidebar          `#FFFFFF`   Sidebar percakapan
  Surface          `#FFFFFF`   Area chat dan panel
  Surface Muted    `#EEF1F7`   Hover dan area sekunder
  Primary          `#315CE8`   Tombol utama dan link aktif
  Primary Hover    `#254BC5`   Hover tombol utama
  Accent           `#7657D6`   Aksen ungu secara terbatas
  Text Primary     `#182033`   Teks utama
  Text Secondary   `#667085`   Teks sekunder
  Border           `#E3E7EF`   Garis pemisah
  Success          `#16845B`   Status berhasil
  Warning          `#B7791F`   Peringatan
  Danger           `#D14343`   Error dan tindakan destruktif

### Dark Mode

Dark Mode menggunakan nuansa blue-charcoal yang berlapis, bukan hitam
pekat atau neon.

  Token            Warna       Penggunaan
  ---------------- ----------- -------------------------------
  App Background   `#151A24`   Latar utama
  Sidebar          `#1B2230`   Sidebar percakapan
  Surface          `#222B3A`   Area chat dan panel
  Surface Muted    `#2A3546`   Hover dan area sekunder
  Primary          `#7895FF`   Tombol dan link aktif
  Primary Hover    `#91A8FF`   Hover tombol utama
  Accent           `#A58AE8`   Aksen ungu secara terbatas
  Text Primary     `#F1F4FA`   Teks utama
  Text Secondary   `#A8B2C3`   Teks sekunder
  Border           `#344052`   Garis pemisah
  Success          `#55C49A`   Status berhasil
  Warning          `#E7B45B`   Peringatan
  Danger           `#F07878`   Error dan tindakan destruktif

Ketentuan: - Semua komponen harus menggunakan design token, bukan warna
hard-coded yang tersebar. - Kontras teks harus tetap terbaca pada kedua
tema. - Status tidak boleh dibedakan hanya melalui warna; gunakan label
atau ikon. - Pengguna dapat memilih Light, Dark, atau System. -
Preferensi tema disimpan secara lokal dan diterapkan tanpa reload.

## 10.3 Typography & Layout

Typography: - Gunakan Inter atau Geist. - Heading memiliki hierarki yang
jelas. - Body text nyaman dibaca. - Metadata seperti timestamp
menggunakan ukuran lebih kecil. - Hindari terlalu banyak ukuran dan
weight font.

Layout: - Desktop menggunakan conversation sidebar dan main content. -
Sidebar memiliki lebar konsisten dan dapat diperkecil jika diperlukan. -
Area chat menggunakan ruang yang tersedia. - Message composer berada di
bagian bawah dengan jarak yang nyaman. - Settings menggunakan navigasi
kategori yang jelas. - Mobile menggunakan navigasi drawer atau halaman
terpisah.

## 10.4 Component Principles

-   Button: ukuran dan hierarchy konsisten; primary action hanya satu
    yang dominan per area.
-   Input: label jelas, helper text seperlunya, dan error state
    informatif.
-   Dialog: digunakan untuk tindakan yang membutuhkan konfirmasi.
-   Popover/context menu: digunakan untuk aksi pesan dan menu tambahan.
-   Toast: untuk hasil tindakan singkat.
-   Badge: hanya untuk status yang benar-benar berguna.
-   Avatar: sederhana dan konsisten.
-   Message bubble: mudah dibedakan antara pesan sendiri dan pesan
    peserta lain.
-   Skeleton/loading state: hanya ketika data benar-benar sedang dimuat.
-   Empty state: memberikan langkah berikutnya, bukan sekadar ilustrasi
    dekoratif.

## 10.5 Landing Page

Tujuan: memperkenalkan SecureLink dan mengarahkan pengguna untuk
memulai.

Komponen: - Logo dan navigasi ringkas. - Headline yang menjelaskan
komunikasi privat antarperangkat. - Deskripsi singkat mengenai P2P dan
keamanan. - CTA Start Chatting. - Ringkasan teknologi: WebRTC, DTLS,
E2EE, dan Tailscale. - Penjelasan singkat cara kerja. - Footer berisi
informasi proyek.

Design: - Layout editorial dengan headline kuat. - Gunakan visual
diagram koneksi sederhana atau ilustrasi produk yang relevan. - Hindari
hero dengan banyak gradient, floating cards, atau angka statistik yang
tidak memiliki data nyata. - Sediakan Light/Dark theme toggle.

## 10.6 User Setup

Komponen: - Display Name. - Generate atau tampilkan User ID. - Save
Identity. - Continue.

Design: - Form ringkas di area fokus. - Helper text menjelaskan bahwa
nama dan User ID adalah identitas sesi, bukan akun terverifikasi. -
Validasi input jelas.

## 10.7 Dashboard / Chat Home

Komponen: - Conversation Sidebar. - Profile pengguna. - Search
Conversations. - New Chat / Create Room. - Join Room. - Recent Rooms. -
Settings. - Welcome state pada area utama.

Recent Rooms hanya menampilkan room yang metadata atau referensinya
masih tersedia pada sesi/perangkat. Jangan menjanjikan sinkronisasi
riwayat chat permanen.

## 10.8 Create Room

Komponen: - Room Name. - Room ID Generator. - PIN Configuration. -
Dynamic PIN Toggle. - Maximum Participants. - E2EE Setting. - Ephemeral
Message Default. - Room Access Setting. - Create Room Button.

Design: - Kelompokkan pengaturan dasar dan keamanan. - Gunakan helper
text untuk opsi teknis. - Pengaturan lanjutan dapat ditempatkan dalam
expandable section. - Setelah room berhasil dibuat, tampilkan Room ID
dan QR Invitation.

## 10.9 Join Room

Komponen: - Room ID Input. - Room PIN jika diwajibkan. - Display Name. -
Scan QR Code. - Join Button. - Connection Status.

Design: - Room ID menjadi fokus utama. - QR scan menjadi alternatif,
bukan satu-satunya cara. - Tampilkan error dan loading state yang jelas.

## 10.10 Chat Room

### Chat Header

-   Room Name.
-   Jumlah peserta.
-   Status koneksi sederhana.
-   Tombol Room Information.
-   Tombol More Options.

More Options: - View Members. - Invite. - Room Settings sesuai izin. -
Copy Room ID. - Leave Room.

### Message Area

-   Message Bubble.
-   Avatar dan Display Name.
-   Timestamp.
-   Status pesan.
-   Reply preview.
-   Emoji Reaction.
-   System Message.
-   Ephemeral Countdown.

Ketentuan: - Pesan sendiri berada di kanan dan pesan peserta lain di
kiri. - Pesan panjang mudah dibaca. - Timestamp tidak mendominasi
bubble. - Pesan sistem memiliki gaya berbeda. - Auto-scroll hanya
dilakukan ketika pengguna berada dekat bagian pesan terbaru. - Jika
pengguna membaca pesan lama, tampilkan affordance untuk kembali ke pesan
terbaru.

### Message Composer

-   Text Input.
-   Send Button.
-   Ephemeral Toggle.
-   Duration Selector.
-   Tombol atau menu interaksi tambahan jika diperlukan.

Keyboard: - Enter untuk mengirim jika preferensi aktif. - Shift + Enter
untuk baris baru. - Composer tidak boleh tertutup keyboard pada mobile.

### Message Context Menu

Aksi: - Reply. - React. - Copy. - Delete for Me. - Delete for Everyone
jika diizinkan.

Menu muncul saat pesan dipilih atau diarahkan, tidak selalu terlihat.

### Connection Indicator

Tampilkan label sederhana: - Connected. - Connecting. - Reconnecting. -
Disconnected.

Detail teknis tersedia melalui Settings.

## 10.11 Room Members & Administration

Komponen: - Daftar anggota. - Label Room Creator. - Status koneksi. -
Invite Participant. - Kick Participant untuk Creator. - Lock/Unlock Room
untuk Creator. - Pengaturan izin room.

Tindakan destruktif harus meminta konfirmasi. Setelah perubahan, peserta
yang relevan menerima System Message.

## 10.12 Settings

Kategori:

**Account** - Avatar opsional. - Display Name. - User ID. - Edit
Profile.

**Appearance** - Light Mode. - Dark Mode. - System Mode.

**Notifications** - Enable Notifications. - Sound Notifications. - New
Message Notifications. - Connection Notifications.

**Chat Preferences** - Default Ephemeral Message. - Default Message
Duration. - Enter to Send. - Message Display Preferences.

**Privacy & Security** - E2EE Status. - Encryption Information. - Key
Information yang aman ditampilkan. - Room PIN Information. - Ephemeral
Message Information. - Security Recommendations.

**Connection** - Connection Type. - Signaling Status. - WebRTC Peer
Connection State. - ICE Connection State. - DataChannel State. - DTLS
Transport State. - Connected Peer Count. - VPN/Tailscale Guidance.

**Diagnostics** - WebSocket Connection Test. - WebRTC Connection Test. -
ICE Candidate Information. - Selected Candidate Pair. - DataChannel
Test. - Connection Latency jika dapat diukur. - Diagnostic Logs.

**About** - Logo SecureLink. - Application Version. - Deskripsi. -
Teknologi yang digunakan. - Informasi proyek praktikum.

## 10.13 Demonstration Page

Halaman khusus untuk presentasi praktikum.

Komponen: - Broadcast Demonstration. - P2P Demonstration. - E2EE
Demonstration. - Ephemeral Message Demonstration. - Connection State. -
Network Information. - Demo Instructions.

Design: - Gunakan diagram alur yang mudah dipahami. - Tampilkan status
aktual, bukan animasi yang hanya bersifat dekoratif. - Beri penjelasan
singkat tentang apa yang sedang diuji. - Sediakan kontrol mulai dan
reset demo. - Pisahkan informasi teknis dari halaman chat sehari-hari.

# 11. USER FLOWS

## 11.1 Initial Setup

1.  Pengguna membuka SecureLink.
2.  Pengguna memilih Start Chatting.
3.  Pengguna memasukkan Display Name.
4.  Sistem membuat User ID.
5.  Pengguna menyimpan identitas.
6.  Pengguna masuk ke Chat Home.

## 11.2 Create Room

1.  Pengguna memilih Create Room.
2.  Pengguna mengisi Room Name.
3.  Sistem membuat Room ID.
4.  Pengguna mengatur PIN dan opsi keamanan.
5.  Pengguna membuat room.
6.  Sistem menampilkan room dan opsi undangan.
7.  Pengguna membagikan Room ID, link, atau QR Code.

## 11.3 Join Room

1.  Pengguna membuka Join Room atau memindai QR.
2.  Sistem mengisi informasi undangan yang tersedia.
3.  Pengguna memasukkan PIN jika diperlukan.
4.  Backend memvalidasi akses.
5.  Pengguna bergabung ke room.
6.  Signaling dimulai.
7.  WebRTC membentuk koneksi.
8.  Aplikasi menampilkan status koneksi aktual.

## 11.4 Sending Message

1.  Pengguna menulis pesan.
2.  Aplikasi memvalidasi input dan status DataChannel.
3.  Aplikasi mengenkripsi pesan jika E2EE aktif.
4.  Pesan dikirim melalui DataChannel.
5.  Penerima menerima ciphertext.
6.  Penerima memverifikasi dan mendekripsi pesan.
7.  Penerima mengirim acknowledgment.
8.  Pengirim memperbarui status menjadi Delivered.

## 11.5 Failed Message

1.  Pengiriman tidak memperoleh acknowledgment dalam batas waktu.
2.  Aplikasi menampilkan status pending atau failed sesuai kondisi.
3.  Aplikasi melakukan retry dengan batas percobaan.
4.  Penerima melakukan deduplication berdasarkan Message ID.
5.  Jika retry gagal, pengguna dapat mencoba mengirim ulang secara
    manual.

## 11.6 Room Administration

1.  Room Creator membuka daftar anggota atau Room Settings.
2.  Creator memilih tindakan.
3.  Sistem memvalidasi hak akses.
4.  Untuk tindakan destruktif, sistem meminta konfirmasi.
5.  Backend menerapkan perubahan.
6.  Sistem mengirim System Message kepada anggota yang relevan.

## 11.7 Ephemeral Message

1.  Pengguna mengaktifkan pesan sementara.
2.  Pengguna memilih durasi.
3.  Pesan dienkripsi dan dikirim.
4.  Penerima menerima dan mendekripsi pesan.
5.  Kedua perangkat menggunakan waktu kedaluwarsa yang sama.
6.  Pesan dihapus dari tampilan dan memori aplikasi setelah kedaluwarsa.

# 12. TECHNICAL ARCHITECTURE

## 12.1 Application Stack

Teknologi:
- **Laravel (PHP):** framework utama untuk backend, routing, validasi, session, otorisasi, dan business logic.
- **Blade:** server-side templating untuk halaman aplikasi.
- **JavaScript:** interaksi frontend dan pengelolaan koneksi real-time pada browser.
- **Tailwind CSS:** styling dan design system.
- **Laravel Echo:** client untuk menerima event broadcast melalui WebSocket.
- **Laravel Reverb:** WebSocket server untuk signaling dan presence room.
- **WebRTC API:** koneksi peer-to-peer dan DataChannel.
- **Web Crypto API:** proses E2EE di sisi browser.

Tanggung jawab frontend/browser:
- UI dan navigasi menggunakan Blade, HTML, CSS, dan JavaScript.
- Identitas sesi dan interaksi room.
- WebRTC peer management.
- SDP Offer/Answer dan ICE Candidate handling melalui signaling.
- Message handling melalui DataChannel.
- E2EE encryption/decryption menggunakan Web Crypto API.
- Message acknowledgment dan retry.
- Theme management, Settings, dan diagnostics.

## 12.2 Backend — Laravel

Teknologi:
- **Laravel (PHP)** sebagai backend utama dan server-rendered web application.
- **Laravel Reverb** sebagai WebSocket server.
- **Laravel Echo** untuk integrasi event broadcast pada browser.
- Laravel Events dan Broadcasting untuk mengirim event signaling dan perubahan status room.
- Laravel Controllers, Form Requests, Middleware, Policies/Gates, dan Services untuk pemisahan tanggung jawab aplikasi.

Tanggung jawab:
- Menyediakan halaman aplikasi melalui route web dan Blade.
- Mengelola session identity, room metadata, membership, dan authorization.
- Memvalidasi Room PIN, Dynamic PIN, invite token, dan batas percobaan.
- Mengelola Room Administration, termasuk kick, lock/unlock, dan permission.
- Menyediakan signaling melalui event WebSocket untuk SDP Offer/Answer dan ICE Candidate.
- Menerapkan rate limiting dan validasi format/ukuran payload.
- Membersihkan room dan sesi kedaluwarsa melalui Laravel Scheduler/Queue bila diperlukan.

Ketentuan implementasi:
- Gunakan Laravel Reverb sebagai pilihan utama WebSocket agar signaling terintegrasi dengan Laravel Broadcasting.
- Gunakan private channel atau presence channel untuk membatasi event berdasarkan room dan membership.
- Otorisasi channel wajib memeriksa sesi serta membership yang sah; jangan hanya mengandalkan Room ID atau Display Name dari browser.
- Laravel hanya menangani signaling dan metadata. Pada mode P2P, Laravel/Reverb tidak meneruskan isi pesan setelah DataChannel terbentuk.
- Pada Broadcast Comparison Mode, pesan memang dikirim ke server untuk diteruskan kepada peserta sesuai tujuan demonstrasi.
- Jangan menyimpan plain-text chat, private key, shared secret, atau isi pesan ephemeral di server.

## 12.3 Database

Teknologi: MySQL direkomendasikan untuk deployment. SQLite dapat digunakan untuk pengembangan lokal atau demonstrasi sederhana.

Data yang dapat disimpan: - Room Metadata. - Room Configuration. - Room
PIN Hash atau metadata secret yang dikelola secara aman. - Session
Metadata. - Invite Token Metadata jika digunakan.

Data yang tidak disimpan: - Plain-text chat. - Private Key. - Shared
Secret. - Isi pesan sementara. - Ciphertext chat secara permanen.

Catatan deployment: Untuk deployment Laravel, MySQL direkomendasikan agar data room, session metadata, dan konfigurasi dikelola secara konsisten. SQLite dapat digunakan untuk development atau demonstrasi satu instance dengan persistent storage. Jika aplikasi berjalan pada beberapa instance, gunakan database bersama dan konfigurasi session/cache/queue yang sesuai; jangan mengandalkan file SQLite lokal yang tidak persisten.

### Laravel Implementation Structure

Struktur implementasi yang disarankan:

- `routes/web.php`: halaman Landing, Setup, Dashboard, Room, Settings, dan Demonstration.
- `routes/channels.php`: otorisasi private/presence channel untuk signaling dan presence room.
- `app/Http/Controllers`: endpoint untuk membuat room, bergabung, keluar, mengelola PIN, undangan, dan administrasi room.
- `app/Http/Requests`: validasi input dan payload dari pengguna.
- `app/Models`: model Room, RoomParticipant, UserSession, dan RoomInvitation sesuai kebutuhan skema.
- `app/Policies` atau Gates: pemeriksaan hak Room Creator dan Participant.
- `app/Events`: event signaling dan perubahan status room yang dibroadcast melalui Reverb.
- `app/Services`: logika room, PIN, invitation, dan aturan bisnis.
- `resources/views`: template Blade untuk seluruh halaman dan komponen UI.
- `resources/js`: JavaScript untuk WebRTC, Web Crypto API, Laravel Echo, chat interaction, dan theme handling.
- `database/migrations`: definisi serta perubahan skema database.
- Laravel Scheduler/Queue: pembersihan metadata room dan session yang kedaluwarsa jika diperlukan.

Catatan: WebRTC dan Web Crypto API tetap berjalan di browser. Laravel tidak menggantikan API browser tersebut; Laravel menyediakan halaman, validasi, otorisasi, penyimpanan metadata, dan signaling.

## 12.4 Network Architecture

-   Tailscale: jaringan privat pada skenario pengujian.
-   WSS melalui Laravel Reverb: signaling.
-   Laravel Echo: menerima event broadcast dari Reverb pada browser.
-   STUN: NAT traversal assistance.
-   TURN: relay fallback.
-   WebRTC DataChannel: pertukaran pesan.
-   DTLS: transport security.
-   Web Crypto API: E2EE.

# 13. DATA MODEL

Model konseptual berikut menjadi dasar implementasi. Detail tipe data
dan relasi final mengikuti rancangan database.

## 13.1 User Session

-   user_id
-   display_name
-   session_id
-   created_at
-   connection_status

## 13.2 Room

-   room_id
-   room_name
-   creator_id
-   room_pin_hash
-   dynamic_pin_enabled
-   max_participants
-   e2ee_enabled
-   ephemeral_enabled
-   is_locked
-   created_at
-   expires_at

## 13.3 Room Participant

-   participant_id
-   room_id
-   user_id
-   role
-   joined_at
-   connection_status

## 13.4 Message (In-memory)

-   message_id
-   room_id
-   sender_id
-   encrypted_payload
-   created_at
-   expires_at
-   message_type
-   delivery_status

## 13.5 Message Acknowledgment

-   acknowledgment_id
-   message_id
-   sender_id
-   receiver_id
-   received_at
-   status

Metadata acknowledgment hanya berlaku selama konteks sesi dan tidak
menjadi penyimpanan riwayat pesan permanen.

## 13.6 Room Invitation

Jika invite token digunakan: - invite_id - room_id - token_hash -
created_by - expires_at - revoked_at - usage_limit

Token mentah tidak disimpan dalam database.

# 14. SECURITY REQUIREMENTS

1.  WSS wajib digunakan pada deployment HTTPS.
2.  WebRTC DataChannel menggunakan DTLS.
3.  E2EE menggunakan AES-GCM.
4.  Pertukaran kunci menggunakan mekanisme derivasi yang aman.
5.  HKDF digunakan untuk derivasi kunci jika sesuai dengan skema
    pertukaran kunci.
6.  IV AES-GCM harus unik untuk setiap enkripsi dengan kunci yang sama.
7.  Private key dan shared secret tidak boleh dikirim ke server.
8.  PIN tidak boleh disimpan sebagai plain-text.
9.  Validasi akses room dilakukan server-side.
10. Batasi percobaan PIN dan signaling.
11. Validasi ukuran dan format pesan.
12. Batasi signaling berdasarkan membership room.
13. Jangan mencatat isi pesan atau rahasia ke security logs.
14. Tangani kegagalan enkripsi dan dekripsi secara aman.
15. Jangan mengklaim P2P jika jalur aktual menggunakan TURN relay.
16. Jangan mengklaim VPN aktif hanya berdasarkan asumsi browser.
17. Jangan mengklaim pesan ephemeral tidak dapat disalin atau direkam.
18. Status E2EE harus dibedakan dari status DTLS.
19. Tindakan administratif harus memvalidasi role di backend.
20. Invite token harus memiliki masa berlaku dan mekanisme pencabutan
    jika digunakan.
21. Pesan retry harus memiliki Message ID dan mekanisme deduplication.
22. Error yang ditampilkan kepada pengguna tidak boleh membocorkan
    secret atau detail internal yang sensitif.

# 15. NON-FUNCTIONAL REQUIREMENTS

## Performance

-   Pesan dikirim secara real-time pada koneksi yang tersedia.
-   UI tetap responsif ketika pesan bertambah.
-   Tidak ada reload halaman saat chatting.
-   Reconnection menggunakan batas percobaan dan backoff.
-   Rendering pesan tidak menyebabkan lag yang mengganggu.

## Reliability

-   Koneksi gagal ditangani dengan status yang jelas.
-   Pesan yang belum mendapatkan acknowledgment tidak langsung dianggap
    Delivered.
-   Retry tidak membuat pesan duplikat.
-   Aplikasi menangani peer yang keluar dan bergabung kembali.
-   Kegagalan dekripsi tidak menampilkan ciphertext sebagai pesan
    normal.

## Usability

-   Create dan Join Room mudah ditemukan.
-   Room ID dan QR Invitation mudah digunakan.
-   Status koneksi dan pesan mudah dipahami.
-   Pengaturan keamanan memiliki helper text.
-   Light Mode dan Dark Mode sama-sama nyaman digunakan.

## Compatibility

-   Google Chrome.
-   Microsoft Edge.
-   Mozilla Firefox.
-   Browser mobile yang mendukung WebRTC dan Web Crypto API.

# 16. TESTING REQUIREMENTS

## 16.1 Functional Testing

-   Membuat dan bergabung ke room.
-   Mengirim dan menerima pesan.
-   Reply dan reaction.
-   Copy dan delete message sesuai izin.
-   Kick participant.
-   Lock dan unlock room.
-   Generate dan scan QR invitation.
-   Mengatur PIN.
-   Mengaktifkan pesan sementara.
-   Mengganti tema.
-   Menjalankan diagnostics.

## 16.2 Network Testing

-   Dua perangkat pada jaringan lokal.
-   Dua perangkat melalui Tailscale.
-   Dua perangkat pada jaringan berbeda.
-   ICE negotiation.
-   STUN dan TURN.
-   Direct connection dan relay detection.
-   Disconnect dan reconnect.
-   DataChannel state.
-   Pengujian pengiriman ketika jaringan tidak stabil.

## 16.3 Security Testing

-   Memastikan payload E2EE tidak terlihat sebagai plain-text pada jalur
    pengiriman.
-   Memastikan signaling server tidak menerima isi pesan P2P.
-   Menguji kegagalan dekripsi.
-   Menguji manipulasi ciphertext.
-   Memastikan private key tidak dikirim ke server.
-   Menguji validasi PIN.
-   Menguji rate limiting.
-   Menguji akses room oleh non-member.
-   Menguji otorisasi Kick Participant dan Lock Room.
-   Memastikan secret tidak muncul di logs.
-   Memastikan QR invitation tidak mengekspos secret.

## 16.4 Delivery Testing

-   Pesan memperoleh acknowledgment.
-   Pesan tanpa acknowledgment ditandai pending atau failed.
-   Retry dilakukan sesuai batas.
-   Pesan duplikat tidak ditampilkan dua kali.
-   Pesan ephemeral tidak dikirim ulang setelah kedaluwarsa.

## 16.5 UI/UX Testing

-   Light Mode.
-   Dark Mode.
-   System Mode.
-   Desktop.
-   Tablet.
-   Mobile.
-   Sidebar dan navigasi.
-   Composer dan keyboard mobile.
-   Dialog konfirmasi.
-   Empty, loading, dan error states.
-   Kontras dan keterbacaan.
-   Context menu pesan.

# 17. ACCEPTANCE CRITERIA

SecureLink dinyatakan memenuhi kebutuhan utama apabila:

1.  Pengguna dapat membuat dan bergabung ke room.
2.  Dua perangkat dapat bertukar pesan.
3.  Signaling server dapat menukar SDP dan ICE Candidate.
4.  WebRTC DataChannel dapat terbentuk.
5.  Jalur koneksi dapat dibedakan antara Direct dan Relay.
6.  DTLS aktif pada transport WebRTC.
7.  E2EE dapat mengenkripsi dan mendekripsi pesan.
8.  Signaling server tidak meneruskan isi pesan pada mode P2P.
9.  PIN dapat membatasi akses room.
10. Pesan sementara kedaluwarsa sesuai durasi.
11. Room Creator dapat mengelola anggota dan mengunci room.
12. Status Sent dan Delivered dibedakan berdasarkan acknowledgment.
13. Retry tidak menghasilkan pesan duplikat.
14. Pengguna dapat membalas dan memberi reaction pada pesan.
15. Pengguna dapat mengundang peserta melalui QR Code atau Room ID.
16. Pengguna dapat mengakses informasi koneksi melalui Settings.
17. Aplikasi memiliki Light Mode, Dark Mode, dan System Mode.
18. Tampilan responsif pada desktop dan mobile.
19. Informasi teknis tidak memenuhi halaman chat utama.
20. Fitur keamanan tidak menampilkan klaim yang tidak dapat dibuktikan.

# 18. PROJECT SCOPE

## In Scope

-   Web-based chat.
-   User session identity.
-   Room management.
-   WebSocket signaling.
-   WebRTC P2P.
-   Tailscale integration guide.
-   STUN/TURN support.
-   DTLS.
-   E2EE menggunakan Web Crypto API.
-   Ephemeral Messaging.
-   Dynamic PIN.
-   Security Information dan Security Log.
-   Room Administration.
-   Reliable Message Delivery.
-   Message Interaction.
-   QR Code Room Invitation.
-   Broadcast Comparison Mode.
-   Network Diagnostics.
-   Demonstration Page.
-   Light Mode dan Dark Mode.
-   Responsive UI.

## Out of Scope

-   Voice Call.
-   Video Call.
-   File Sharing.
-   Cloud Message Synchronization.
-   Permanent Chat History.
-   Integrasi akun media sosial.
-   Group P2P mesh berskala besar.
-   Peer Identity Verification sebagai fitur produk khusus.
-   Live Network Topology.
-   Security Event Timeline terpisah.
-   Network Performance Monitoring sebagai dashboard khusus.

# 19. DEVELOPMENT PRIORITY

## Phase 1 --- Foundation & UI

-   Setup project Laravel dan konfigurasi environment.
-   Setup Blade, Tailwind CSS, JavaScript, serta Laravel Reverb/Echo.
-   Design system dan theme tokens.
-   Landing Page.
-   User Identity.
-   Dashboard.
-   Create Room.
-   Join Room.
-   Responsive Chat UI.
-   Light/Dark/System Mode.

## Phase 2 --- Signaling & Basic Messaging

-   WebSocket signaling.
-   Room membership.
-   SDP Offer/Answer.
-   ICE Candidate Exchange.
-   Broadcast Mode.
-   Basic messaging.
-   Disconnect handling.

## Phase 3 --- WebRTC P2P

-   RTCPeerConnection.
-   RTCDataChannel.
-   STUN/TURN.
-   Direct/Relay detection.
-   P2P messaging.
-   Reconnection.

## Phase 4 --- Security

-   DTLS verification.
-   E2EE key exchange.
-   HKDF key derivation.
-   AES-GCM.
-   Nonce/IV management.
-   Encryption error handling.
-   Security Information.
-   Security Logs.

## Phase 5 --- Room & Messaging Enhancements

-   Room Administration.
-   Lock Room.
-   Kick Participant.
-   Reliable Message Delivery.
-   Acknowledgment.
-   Retry dan deduplication.
-   Reply to Message.
-   Emoji Reaction.
-   Copy/Delete Message.

## Phase 6 --- Privacy & Invitation

-   Ephemeral Messaging.
-   Dynamic PIN.
-   QR Code Invitation.
-   Invite token expiration/revocation jika digunakan.

## Phase 7 --- Settings & Diagnostics

-   Settings.
-   Connection Information.
-   Security Information.
-   Network Diagnostics.
-   Tailscale guidance.
-   Demonstration Page.

## Phase 8 --- Testing & Refinement

-   Multi-device testing.
-   Wireshark testing.
-   Security testing.
-   Delivery testing.
-   Responsive testing.
-   Light/Dark visual refinement.
-   Bug fixing.
-   Demo preparation.

# 20. PROJECT SUCCESS METRICS

-   Dua perangkat berhasil terhubung dan bertukar pesan.
-   WebRTC DataChannel berjalan.
-   Direct dan Relay dapat dibedakan dengan benar.
-   DTLS aktif.
-   E2EE berhasil mengenkripsi dan mendekripsi pesan.
-   Signaling server tidak menerima isi pesan P2P.
-   PIN membatasi akses room.
-   Ephemeral messaging berjalan sesuai masa berlaku.
-   Room Creator dapat mengelola anggota.
-   Acknowledgment memperbarui status Delivered.
-   Retry tidak menghasilkan duplikasi pesan.
-   QR Invitation membantu pengguna bergabung.
-   Pengguna dapat menggunakan aplikasi tanpa kesulitan memahami
    navigasi.
-   Light Mode dan Dark Mode konsisten.
-   Aplikasi dapat digunakan pada desktop dan mobile.
-   Demonstrasi dapat menjelaskan perbedaan Broadcast dan P2P.

------------------------------------------------------------------------

# APPENDIX A --- DESIGN QUALITY CHECKLIST

Sebelum UI dinyatakan selesai, pastikan:

-   [ ] Light Mode dan Dark Mode dirancang sebagai dua tema yang
    sama-sama matang.
-   [ ] Dark Mode menggunakan lapisan blue-charcoal, bukan hitam pekat
    di semua area.
-   [ ] Tidak semua bagian dibuat menjadi card.
-   [ ] Chat layout mengikuti pola aplikasi messaging modern tanpa menyalin produk lain.
-   [ ] Conversation sidebar, message bubble, dan composer memiliki hierarki yang jelas.
-   [ ] Tidak ada dashboard generik, dekorasi berlebihan, atau panel statistik yang tidak diperlukan.
-   [ ] Tampilan mobile menggunakan alur navigasi yang sesuai, bukan sekadar desktop yang diperkecil.
-   [ ] Gradient hanya digunakan jika memiliki fungsi visual.
-   [ ] Tidak ada glow atau glass effect berlebihan.
-   [ ] Tipografi memiliki hierarki yang konsisten.
-   [ ] Spacing dan alignment konsisten.
-   [ ] Tombol utama memiliki prioritas visual yang jelas.
-   [ ] Status koneksi dan keamanan menggunakan data aktual.
-   [ ] Empty, loading, success, dan error states dirancang.
-   [ ] Tampilan mobile tidak terasa seperti desktop yang diperkecil.
-   [ ] Interaksi pesan tetap ringkas dan tidak mengganggu pembacaan.

# APPENDIX B --- SECURITY CLAIMS

Gunakan istilah secara akurat:

  -----------------------------------------------------------------------
  Klaim                               Ketentuan
  ----------------------------------- -----------------------------------
  Encrypted in transit                Didukung oleh transport WebRTC
                                      dengan DTLS.

  End-to-End Encrypted                Hanya ditampilkan jika enkripsi
                                      aplikasi berhasil diinisialisasi
                                      dan diterapkan pada pesan.

  Direct P2P                          Hanya ditampilkan jika selected ICE
                                      candidate pair menunjukkan jalur
                                      langsung.

  Relay                               Ditampilkan jika jalur koneksi
                                      menggunakan TURN relay.

  VPN Connected                       Jangan diklaim hanya berdasarkan
                                      status browser; tampilkan hasil
                                      pemeriksaan yang benar-benar
                                      tersedia.

  Message Delivered                   Hanya setelah acknowledgment dari
                                      perangkat penerima diterima.

  Ephemeral                           Menjelaskan penghapusan dari
                                      aplikasi setelah kedaluwarsa, bukan
                                      jaminan penghapusan seluruh salinan
                                      eksternal.
  -----------------------------------------------------------------------

------------------------------------------------------------------------

**END OF DOCUMENT**
