@extends('layouts.app')

@section('title', 'Gabung Room — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-10">
    <div class="sl-surface rounded-xl p-6 sm:p-8 space-y-5">
        <div class="text-center space-y-1.5">
            <h1 class="text-xl font-bold text-[var(--sl-text-primary)]">Gabung ke Room</h1>
            <p class="text-xs text-[var(--sl-text-secondary)]">
                Pindai QR Code undangan atau masukkan Room ID secara manual.
            </p>
        </div>

        <!-- Method Switcher Tabs -->
        <div class="grid grid-cols-2 gap-1 p-1 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] text-xs font-semibold">
            <button type="button" id="tab-manual-btn" onclick="switchJoinTab('manual')"
                class="py-2 rounded-md bg-[var(--sl-white-surface)] text-[var(--sl-primary-teal)] shadow-2xs transition">
                Room ID / Tautan
            </button>
            <button type="button" id="tab-qr-btn" onclick="switchJoinTab('qr')"
                class="py-2 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition">
                Scan QR Code
            </button>
        </div>

        <!-- Method 1: Manual Room ID & PIN Form -->
        <div id="panel-manual">
            <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" id="manual-invite-token" name="invite_token" value="{{ $inviteToken }}">

                <div>
                    <label for="room_id" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Room ID <span class="text-[#EC643E]">*</span>
                    </label>
                    <input type="text" id="room_id" name="room_id" required
                        value="{{ old('room_id', $prefilledRoomId) }}"
                        placeholder="Contoh: SEC-8F2-9K1"
                        class="w-full px-3.5 py-2.5 rounded-lg sl-input text-sm font-mono uppercase">
                </div>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">
                        Room PIN (Jika Diwajibkan)
                    </label>
                    <input type="password" id="pin" name="pin" maxlength="16"
                        placeholder="PIN statis atau Dynamic PIN 6-digit"
                        class="w-full px-3.5 py-2.5 rounded-lg sl-input text-sm">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg sl-btn-coral font-semibold text-sm">
                    Gabung ke Room
                </button>
            </form>
        </div>

        <!-- Method 2: Functional QR Code Scanner -->
        <div id="panel-qr" class="hidden space-y-4">
            <div class="p-3 rounded-lg bg-[var(--sl-main-surface)] border border-[var(--sl-divider)] flex flex-col items-center space-y-3">
                <div class="w-full max-w-[240px] aspect-square rounded-lg bg-[#20292B] overflow-hidden relative flex items-center justify-center">
                    <video id="qr-video" class="w-full h-full object-cover hidden" playsinline></video>
                    <canvas id="qr-canvas" class="hidden"></canvas>
                    <div id="qr-placeholder" class="text-center p-4 space-y-2 text-white/80">
                        <svg class="w-8 h-8 mx-auto opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <div class="text-[11px]">Arahkan kamera ke QR Code undangan SecureLink</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full">
                    <button type="button" id="btn-start-scan" onclick="beginCameraScan()" class="flex-1 py-2 px-3 rounded-lg sl-btn-primary text-xs font-semibold">
                        Aktifkan Kamera
                    </button>
                    <button type="button" id="btn-stop-scan" onclick="haltCameraScan()" class="hidden flex-1 py-2 px-3 rounded-lg sl-btn-secondary text-xs font-semibold">
                        Tutup Kamera
                    </button>
                    <label class="py-2 px-3 rounded-lg sl-btn-secondary text-xs font-semibold cursor-pointer text-center">
                        Unggah QR
                        <input type="file" accept="image/*" class="hidden" onchange="handleQrFileUpload(this)">
                    </label>
                </div>
            </div>

            <!-- Feedback Message -->
            <div id="qr-feedback" class="hidden p-3 rounded-lg text-xs font-medium"></div>

            <!-- Manual Invitation URL Paste Fallback -->
            <div class="space-y-1.5">
                <label for="qr-manual-link" class="block text-xs font-semibold text-[var(--sl-text-primary)]">
                    Atau Tempel Tautan Undangan
                </label>
                <div class="flex gap-2">
                    <input type="text" id="qr-manual-link" placeholder="https://.../rooms/join?room_id=..."
                        class="flex-1 px-3 py-2 rounded-lg sl-input text-xs font-mono">
                    <button type="button" onclick="validateManualPaste()" class="px-3 py-2 rounded-lg sl-btn-secondary text-xs font-semibold shrink-0">
                        Cek
                    </button>
                </div>
            </div>

            <!-- Room Preview & Confirmation Card -->
            <div id="qr-confirm-card" class="hidden p-4 rounded-lg bg-[var(--sl-soft-teal)] border border-[var(--sl-secondary-teal)]/40 space-y-3">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[var(--sl-primary-teal)]">Undangan Valid</div>
                    <div id="qr-room-name" class="text-sm font-bold text-[var(--sl-text-primary)] mt-0.5"></div>
                    <div id="qr-room-meta" class="text-[11px] text-[var(--sl-text-secondary)]"></div>
                </div>

                <form action="{{ route('rooms.join.submit') }}" method="POST" class="space-y-2.5">
                    @csrf
                    <input type="hidden" id="qr-confirm-room-id" name="room_id">
                    <input type="hidden" id="qr-confirm-token" name="invite_token">

                    <div id="qr-pin-group" class="hidden">
                        <label for="qr-confirm-pin" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1">Room PIN</label>
                        <input type="password" id="qr-confirm-pin" name="pin" maxlength="16" placeholder="Masukkan PIN room"
                            class="w-full px-3 py-2 rounded-lg sl-input text-xs">
                    </div>

                    <button type="submit" id="qr-confirm-submit" class="w-full py-2 px-4 rounded-lg sl-btn-coral text-xs font-semibold">
                        Konfirmasi &amp; Gabung Room
                    </button>
                </form>
            </div>
        </div>

        <div class="pt-4 border-t border-[var(--sl-divider)] text-center text-xs text-[var(--sl-text-secondary)]">
            Ingin membuat room baru?
            <a href="{{ route('rooms.create') }}" class="text-[var(--sl-primary-teal)] font-semibold hover:underline">Buat Room</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function switchJoinTab(tab) {
        const manualPanel = document.getElementById('panel-manual');
        const qrPanel = document.getElementById('panel-qr');
        const manualBtn = document.getElementById('tab-manual-btn');
        const qrBtn = document.getElementById('tab-qr-btn');

        if (tab === 'qr') {
            manualPanel.classList.add('hidden');
            qrPanel.classList.remove('hidden');
            qrBtn.className = 'py-2 rounded-md bg-[var(--sl-white-surface)] text-[var(--sl-primary-teal)] shadow-2xs transition';
            manualBtn.className = 'py-2 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
        } else {
            haltCameraScan();
            qrPanel.classList.add('hidden');
            manualPanel.classList.remove('hidden');
            manualBtn.className = 'py-2 rounded-md bg-[var(--sl-white-surface)] text-[var(--sl-primary-teal)] shadow-2xs transition';
            qrBtn.className = 'py-2 rounded-md text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] transition';
        }
    }

    function showQrFeedback(type, message) {
        const el = document.getElementById('qr-feedback');
        el.classList.remove('hidden', 'bg-[#EC643E]/10', 'text-[#EC643E]', 'bg-[var(--sl-soft-teal)]', 'text-[var(--sl-primary-teal)]');
        if (type === 'error') {
            el.classList.add('bg-[#EC643E]/10', 'text-[#EC643E]');
        } else {
            el.classList.add('bg-[var(--sl-soft-teal)]', 'text-[var(--sl-primary-teal)]');
        }
        el.textContent = message;
    }

    function beginCameraScan() {
        const videoEl = document.getElementById('qr-video');
        const canvasEl = document.getElementById('qr-canvas');
        const placeholder = document.getElementById('qr-placeholder');
        const startBtn = document.getElementById('btn-start-scan');
        const stopBtn = document.getElementById('btn-stop-scan');

        document.getElementById('qr-feedback').classList.add('hidden');
        document.getElementById('qr-confirm-card').classList.add('hidden');

        videoEl.classList.remove('hidden');
        placeholder.classList.add('hidden');
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');

        window.startQrScanner?.({
            videoEl,
            canvasEl,
            onDetected: (rawText) => {
                haltCameraScan();
                handleScannedPayload(rawText);
            },
            onError: (_code, msg) => {
                haltCameraScan();
                showQrFeedback('error', msg);
            },
        });
    }

    function haltCameraScan() {
        window.stopQrScanner?.();
        document.getElementById('qr-video')?.classList.add('hidden');
        document.getElementById('qr-placeholder')?.classList.remove('hidden');
        document.getElementById('btn-start-scan')?.classList.remove('hidden');
        document.getElementById('btn-stop-scan')?.classList.add('hidden');
    }

    async function handleQrFileUpload(input) {
        const file = input.files?.[0];
        if (!file) return;
        try {
            const rawText = await window.scanQrFromFile(file);
            await handleScannedPayload(rawText);
        } catch (err) {
            showQrFeedback('error', err.message || 'QR Code tidak terbaca.');
        } finally {
            input.value = '';
        }
    }

    function validateManualPaste() {
        const val = document.getElementById('qr-manual-link').value.trim();
        if (!val) {
            showQrFeedback('error', 'Masukkan tautan undangan atau Room ID terlebih dahulu.');
            return;
        }
        handleScannedPayload(val);
    }

    async function handleScannedPayload(rawText) {
        const parsed = window.parseInvitationInput?.(rawText);
        if (!parsed || !parsed.roomId) {
            showQrFeedback('error', 'Format QR Code atau tautan undangan tidak valid.');
            return;
        }

        showQrFeedback('info', 'Memvalidasi undangan dengan server...');

        try {
            const res = await fetch('/api/rooms/validate-invite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    room_id: parsed.roomId,
                    invite_token: parsed.inviteToken || '',
                }),
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                document.getElementById('qr-confirm-card').classList.add('hidden');
                showQrFeedback('error', data.message || 'Undangan tidak valid atau sudah kedaluwarsa.');
                return;
            }

            const room = data.room;
            document.getElementById('qr-feedback').classList.add('hidden');
            document.getElementById('qr-room-name').textContent = room.room_name;
            document.getElementById('qr-room-meta').textContent =
                `${room.room_id} • ${room.is_group ? 'Group Chat' : 'Private Chat'} • ${room.active_participants_count}/${room.max_participants} peserta`;

            document.getElementById('qr-confirm-room-id').value = room.room_id;
            document.getElementById('qr-confirm-token').value = parsed.inviteToken || '';

            const pinGroup = document.getElementById('qr-pin-group');
            if (room.requires_pin) pinGroup.classList.remove('hidden');
            else pinGroup.classList.add('hidden');

            const submitBtn = document.getElementById('qr-confirm-submit');
            submitBtn.textContent = room.already_member ? 'Buka Percakapan (Sudah Bergabung)' : 'Konfirmasi & Gabung Room';

            document.getElementById('qr-confirm-card').classList.remove('hidden');
        } catch (err) {
            showQrFeedback('error', 'Gagal memvalidasi undangan ke server.');
        }
    }

    window.addEventListener('beforeunload', () => haltCameraScan());
</script>
@endpush
@endsection
