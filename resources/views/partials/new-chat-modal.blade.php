<!-- Unified New Chat Modal (4 Actions: Private Chat, Create Group, Join Room, Scan QR Code) -->
<div id="new-chat-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
    <div class="sl-surface rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl max-h-[92vh] overflow-y-auto sl-scroll">
        <div class="flex items-center justify-between pb-2.5 border-b border-[var(--sl-divider)]">
            <div class="flex items-center gap-2">
                <button type="button" id="nc-back-btn" onclick="showNewChatView('menu')" class="hidden p-1 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-hover)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h3 id="nc-modal-title" class="text-sm font-bold text-[var(--sl-text-primary)]">New Chat</h3>
            </div>
            <button type="button" onclick="closeNewChatModal()" class="text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] text-lg font-bold leading-none">&times;</button>
        </div>

        <!-- View 0: 4-Option Menu -->
        <div id="nc-view-menu" class="space-y-2">
            <button type="button" onclick="showNewChatView('private')" class="w-full p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] flex items-center gap-3 text-left transition">
                <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Private Chat</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Mulai percakapan pribadi 1 lawan 1 terenkripsi</div>
                </div>
            </button>

            <button type="button" onclick="showNewChatView('group')" class="w-full p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] flex items-center gap-3 text-left transition">
                <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Create Group</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Buat room grup P2P Mesh dengan multi-anggota</div>
                </div>
            </button>

            <button type="button" onclick="showNewChatView('join')" class="w-full p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] flex items-center gap-3 text-left transition">
                <div class="w-9 h-9 rounded-lg bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Join Room</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Gabung menggunakan Room ID atau tautan undangan</div>
                </div>
            </button>

            <button type="button" onclick="showNewChatView('qr')" class="w-full p-3 rounded-lg border border-[var(--sl-divider)] hover:bg-[var(--sl-hover)] flex items-center gap-3 text-left transition">
                <div class="w-9 h-9 rounded-lg bg-[var(--sl-accent-coral)]/15 text-[var(--sl-accent-coral)] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/><path d="M17 17h4v4h-4z"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-[var(--sl-text-primary)]">Scan QR Code</div>
                    <div class="text-[11px] text-[var(--sl-text-secondary)]">Pindai QR undangan via kamera atau tempel link</div>
                </div>
            </button>
        </div>

        <!-- View 1: Private Chat Form -->
        <form id="nc-view-private" action="{{ route('rooms.store.web') }}" method="POST" class="hidden space-y-3.5">
            @csrf
            <input type="hidden" name="max_participants" value="2">
            <input type="hidden" name="e2ee_enabled" value="1">
            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Nama Kontak / Percakapan</label>
                <input type="text" name="room_name" required maxlength="100" placeholder="Contoh: Chat dengan Raka" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">PIN Akses (Opsional)</label>
                <input type="password" name="pin" maxlength="16" placeholder="Kosongkan jika pakai link/QR undangan" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg sl-btn-coral text-xs font-semibold">Mulai Private Chat</button>
        </form>

        <!-- View 2: Create Group Form -->
        <form id="nc-view-group" action="{{ route('rooms.store.web') }}" method="POST" class="hidden space-y-3.5">
            @csrf
            <input type="hidden" name="e2ee_enabled" value="1">
            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Nama Grup</label>
                <input type="text" name="room_name" required maxlength="100" placeholder="Contoh: Tim Keamanan Jaringan" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Maks. Anggota</label>
                    <input type="number" name="max_participants" min="3" max="50" value="10" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">PIN (Opsional)</label>
                    <input type="password" name="pin" maxlength="16" placeholder="Opsional" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
                </div>
            </div>
            <label class="flex items-center gap-2 text-xs text-[var(--sl-text-secondary)] cursor-pointer">
                <input type="checkbox" name="dynamic_pin_enabled" value="1" class="rounded">
                <span>Aktifkan Dynamic PIN (TOTP 30 detik)</span>
            </label>
            <button type="submit" class="w-full py-2.5 rounded-lg sl-btn-coral text-xs font-semibold">Buat Group Chat</button>
        </form>

        <!-- View 3: Join Room Form -->
        <form id="nc-view-join" action="{{ route('rooms.join.submit') }}" method="POST" class="hidden space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Room ID</label>
                <input type="text" name="room_id" required placeholder="Contoh: SEC-8F2-9K1" class="w-full px-3 py-2 rounded-lg sl-input text-xs font-mono uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">PIN Room (Jika Ada)</label>
                <input type="password" name="pin" maxlength="16" placeholder="PIN statis atau Dynamic PIN" class="w-full px-3 py-2 rounded-lg sl-input text-xs">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg sl-btn-primary text-xs font-semibold">Gabung Room</button>
        </form>

        <!-- View 4: Scan QR Code -->
        <div id="nc-view-qr" class="hidden space-y-3">
            <div class="p-3 rounded-lg bg-[var(--sl-chat-bg)] border border-[var(--sl-divider)] flex flex-col items-center space-y-2.5">
                <div class="w-full max-w-[210px] aspect-square rounded-lg bg-[#182425] overflow-hidden relative flex items-center justify-center">
                    <video id="nc-qr-video" class="w-full h-full object-cover hidden" playsinline></video>
                    <canvas id="nc-qr-canvas" class="hidden"></canvas>
                    <div id="nc-qr-placeholder" class="text-center p-3 text-xs text-[#A5B6B7]">
                        Klik Aktifkan Kamera untuk memindai QR Code
                    </div>
                </div>
                <div class="flex gap-2 w-full">
                    <button type="button" id="nc-btn-start-scan" onclick="ncStartQrScan()" class="flex-1 py-1.5 px-2.5 rounded-lg sl-btn-primary text-xs font-semibold">Aktifkan Kamera</button>
                    <button type="button" id="nc-btn-stop-scan" onclick="ncStopQrScan()" class="hidden flex-1 py-1.5 px-2.5 rounded-lg sl-btn-secondary text-xs font-semibold">Tutup Kamera</button>
                    <label class="py-1.5 px-2.5 rounded-lg sl-btn-secondary text-xs font-semibold cursor-pointer">
                        Unggah QR
                        <input type="file" accept="image/*" class="hidden" onchange="ncHandleQrFile(this)">
                    </label>
                </div>
            </div>

            <div id="nc-qr-feedback" class="hidden p-2.5 rounded-lg text-xs font-medium"></div>

            <div class="flex gap-2">
                <input type="text" id="nc-qr-manual" placeholder="Atau tempel link undangan..." class="flex-1 px-3 py-1.5 rounded-lg sl-input text-xs font-mono">
                <button type="button" onclick="ncValidateManual()" class="px-3 py-1.5 rounded-lg sl-btn-secondary text-xs font-semibold">Cek</button>
            </div>

            <div id="nc-qr-confirm" class="hidden p-3 rounded-lg bg-[var(--sl-soft-teal)] border border-[var(--sl-divider)] space-y-2">
                <div id="nc-qr-room-name" class="text-xs font-bold text-[var(--sl-text-primary)]"></div>
                <div id="nc-qr-room-meta" class="text-[11px] text-[var(--sl-text-secondary)]"></div>
                <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="hidden" id="nc-confirm-room-id" name="room_id">
                    <input type="hidden" id="nc-confirm-token" name="invite_token">
                    <input type="password" id="nc-confirm-pin" name="pin" placeholder="Masukkan PIN room" class="hidden w-full px-2.5 py-1.5 rounded sl-input text-xs">
                    <button type="submit" class="w-full py-2 rounded-lg sl-btn-coral text-xs font-semibold">Konfirmasi &amp; Gabung</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openNewChatModal(view = 'menu') {
        document.getElementById('new-chat-modal')?.classList.remove('hidden');
        showNewChatView(view);
    }
    window.openNewChatModal = openNewChatModal;

    function closeNewChatModal() {
        ncStopQrScan();
        document.getElementById('new-chat-modal')?.classList.add('hidden');
    }
    window.closeNewChatModal = closeNewChatModal;

    function showNewChatView(view) {
        ncStopQrScan();
        const views = ['menu', 'private', 'group', 'join', 'qr'];
        views.forEach(v => document.getElementById('nc-view-' + v)?.classList.add('hidden'));
        document.getElementById('nc-view-' + view)?.classList.remove('hidden');

        const backBtn = document.getElementById('nc-back-btn');
        const titleEl = document.getElementById('nc-modal-title');
        const titles = {
            menu: 'New Chat',
            private: 'New Private Chat',
            group: 'Create Group',
            join: 'Join Room',
            qr: 'Scan QR Code',
        };
        if (titleEl) titleEl.textContent = titles[view] || 'New Chat';
        if (backBtn) backBtn.classList.toggle('hidden', view === 'menu');
    }

    function ncStartQrScan() {
        const video = document.getElementById('nc-qr-video');
        const canvas = document.getElementById('nc-qr-canvas');
        const placeholder = document.getElementById('nc-qr-placeholder');
        document.getElementById('nc-qr-feedback').classList.add('hidden');
        document.getElementById('nc-qr-confirm').classList.add('hidden');

        video.classList.remove('hidden');
        placeholder.classList.add('hidden');
        document.getElementById('nc-btn-start-scan').classList.add('hidden');
        document.getElementById('nc-btn-stop-scan').classList.remove('hidden');

        window.startQrScanner?.({
            videoEl: video,
            canvasEl: canvas,
            onDetected: (raw) => { ncStopQrScan(); ncValidatePayload(raw); },
            onError: (_c, msg) => { ncStopQrScan(); ncShowFeedback('error', msg); },
        });
    }

    function ncStopQrScan() {
        window.stopQrScanner?.();
        document.getElementById('nc-qr-video')?.classList.add('hidden');
        document.getElementById('nc-qr-placeholder')?.classList.remove('hidden');
        document.getElementById('nc-btn-start-scan')?.classList.remove('hidden');
        document.getElementById('nc-btn-stop-scan')?.classList.add('hidden');
    }

    async function ncHandleQrFile(input) {
        const file = input.files?.[0];
        if (!file) return;
        try {
            const raw = await window.scanQrFromFile(file);
            await ncValidatePayload(raw);
        } catch (err) {
            ncShowFeedback('error', err.message || 'QR Code tidak terbaca.');
        } finally {
            input.value = '';
        }
    }

    function ncValidateManual() {
        const val = document.getElementById('nc-qr-manual').value.trim();
        if (!val) return ncShowFeedback('error', 'Masukkan tautan undangan atau Room ID.');
        ncValidatePayload(val);
    }

    function ncShowFeedback(type, msg) {
        const el = document.getElementById('nc-qr-feedback');
        el.className = 'p-2.5 rounded-lg text-xs font-medium ' + (type === 'error'
            ? 'bg-[var(--sl-accent-coral)]/15 text-[var(--sl-accent-coral)]'
            : 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]');
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    async function ncValidatePayload(raw) {
        const parsed = window.parseInvitationInput?.(raw);
        if (!parsed || !parsed.roomId) {
            return ncShowFeedback('error', 'Format QR Code atau tautan tidak valid.');
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            const res = await fetch('/api/rooms/validate-invite', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ room_id: parsed.roomId, invite_token: parsed.inviteToken || '' }),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                document.getElementById('nc-qr-confirm').classList.add('hidden');
                return ncShowFeedback('error', data.message || 'Undangan tidak valid.');
            }
            const r = data.room;
            document.getElementById('nc-qr-feedback').classList.add('hidden');
            document.getElementById('nc-qr-room-name').textContent = r.room_name;
            document.getElementById('nc-qr-room-meta').textContent = `${r.room_id} • ${r.is_group ? 'Group' : 'Private'} • ${r.active_participants_count}/${r.max_participants} anggota`;
            document.getElementById('nc-confirm-room-id').value = r.room_id;
            document.getElementById('nc-confirm-token').value = parsed.inviteToken || '';
            document.getElementById('nc-confirm-pin').classList.toggle('hidden', !r.requires_pin);
            document.getElementById('nc-qr-confirm').classList.remove('hidden');
        } catch (e) {
            ncShowFeedback('error', 'Gagal memvalidasi undangan.');
        }
    }
</script>
