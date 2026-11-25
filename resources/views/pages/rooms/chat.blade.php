@extends('layouts.app')

@section('title', $room->room_name . ' — SecureLink Chat')

@section('content')
<div class="h-[calc(100vh-10rem)] min-h-[550px] flex flex-col bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl shadow-sm overflow-hidden">

    <!-- Chat Header -->
    <div class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-[#1f293d] bg-slate-50/70 dark:bg-[#151d30]/70 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">{{ $room->room_name }}</h2>
                    @if($room->isLocked())
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-bold">Terkunci</span>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                    <span>ID: {{ $room->room_id }}</span>
                    <button type="button" onclick="copyRoomId('{{ $room->room_id }}')" class="text-blue-600 dark:text-blue-400 hover:underline text-[11px]" title="Salin ID">Salin</button>
                    <span>&bull;</span>
                    <span id="header-conn-status" class="text-emerald-500 font-semibold">● P2P Direct</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Dynamic PIN Display for Creator -->
            @if($metadata['is_creator'] && !empty($metadata['current_dynamic_pin']))
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs">
                    <span class="text-amber-700 dark:text-amber-400 font-medium">Dynamic PIN:</span>
                    <span class="font-mono font-bold text-amber-900 dark:text-amber-200 tracking-wider">{{ $metadata['current_dynamic_pin'] }}</span>
                </div>
            @endif

            <!-- Invite Button -->
            <button type="button" onclick="openInviteModal()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-[#1f293d] hover:bg-slate-100 dark:hover:bg-[#1f293d] text-xs font-semibold text-slate-700 dark:text-slate-200 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Undang
            </button>

            <!-- Toggle Members Drawer -->
            <button type="button" onclick="toggleMembersDrawer()" class="px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span id="members-count">{{ count($metadata['participants']) }}</span> Peserta
            </button>

            <!-- Leave Room Form -->
            <form action="{{ route('rooms.leave.web', $room->room_id) }}" method="POST" onsubmit="return confirm('Keluar dari room ini?')">
                @csrf
                <button type="submit" class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Keluar dari Room">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Chat Body: Messages + Sliding Members Panel -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- Message List Container -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            <!-- System Welcome Message -->
            <div class="text-center my-2">
                <div class="inline-block px-3.5 py-1.5 rounded-full bg-slate-100 dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] text-[11px] text-slate-500 dark:text-slate-400">
                    🔒 Ruang terenkripsi E2EE didukung WebRTC DataChannel. Pesan tidak disimpan di server.
                </div>
            </div>

            <!-- Messages Area (populated dynamically via JS or server events) -->
            <div id="messages-list" class="space-y-4">
                <!-- Example initial greeting -->
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                        S
                    </div>
                    <div class="flex flex-col items-start max-w-[80%]">
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mb-0.5">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">SecureLink System</span>
                            <span>{{ now()->format('H:i') }}</span>
                        </div>
                        <div class="p-3 rounded-2xl rounded-tl-sm bg-slate-100 dark:bg-[#151d30] border border-slate-200/60 dark:border-[#1f293d] text-xs text-slate-800 dark:text-slate-200 leading-relaxed">
                            Selamat datang di room <strong>{{ $room->room_name }}</strong>. Mulai kirim pesan langsung antar-peer secara aman.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members & Room Administration Sidebar Drawer -->
        <aside id="members-drawer" class="w-72 border-l border-slate-200 dark:border-[#1f293d] bg-slate-50/50 dark:bg-[#111827] p-4 flex flex-col justify-between overflow-y-auto transition-all shrink-0">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-[#1f293d]">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Daftar Anggota</h3>
                    <span class="text-[11px] text-slate-400">{{ count($metadata['participants']) }} / {{ $room->max_participants }}</span>
                </div>

                <div class="space-y-2">
                    @foreach($metadata['participants'] as $p)
                        <div class="p-2 rounded-xl bg-white dark:bg-[#151d30] border border-slate-200 dark:border-[#1f293d] flex items-center justify-between gap-2 shadow-2xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background-color: {{ $p['avatar_color'] }}">
                                    {{ substr($p['display_name'], 0, 1) }}
                                </div>
                                <div class="truncate">
                                    <div class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $p['display_name'] }}</div>
                                    <div class="flex items-center gap-1">
                                        @if($p['role'] === 'creator')
                                            <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400">Creator</span>
                                        @else
                                            <span class="text-[9px] text-slate-400">Anggota</span>
                                        @endif
                                        <span class="text-[8px] text-emerald-500 font-mono">● online</span>
                                    </div>
                                </div>
                            </div>

                            @if($metadata['is_creator'] && $p['user_id'] !== $user->user_id)
                                <button type="button" onclick="kickUser('{{ $p['user_id'] }}', '{{ $p['display_name'] }}')" class="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-[10px] font-semibold" title="Keluarkan Peserta">
                                    Kick
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Creator Administration Controls -->
            @if($metadata['is_creator'])
                <div class="pt-4 border-t border-slate-200 dark:border-[#1f293d] space-y-2">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kontrol Creator</h4>
                    <button type="button" onclick="toggleRoomLock()" class="w-full py-1.5 px-3 rounded-lg border border-slate-300 dark:border-[#1f293d] text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#1f293d] transition flex items-center justify-center gap-1.5">
                        <span id="lock-btn-label">{{ $room->isLocked() ? 'Buka Kunci Room' : 'Kunci Room' }}</span>
                    </button>
                </div>
            @endif
        </aside>
    </div>

    <!-- Message Composer -->
    <div class="p-3 sm:p-4 border-t border-slate-200 dark:border-[#1f293d] bg-white dark:bg-[#111827] shrink-0">
        <form id="message-form" onsubmit="handleSendMessage(event)" class="flex items-center gap-2">
            <!-- Ephemeral Duration Selector -->
            <select id="ephemeral_duration" class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-[#1f293d] bg-slate-50 dark:bg-[#151d30] text-xs text-slate-600 dark:text-slate-300 focus:outline-none">
                <option value="0">Normal</option>
                <option value="10">10s Ephemeral</option>
                <option value="30">30s Ephemeral</option>
                <option value="60">60s Ephemeral</option>
                <option value="300">5m Ephemeral</option>
            </select>

            <input type="text" id="message-input" placeholder="Ketik pesan Anda di sini..." autocomplete="off"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-slate-50/50 dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition flex items-center justify-center gap-1.5 shrink-0">
                <span>Kirim</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>
</div>

<!-- Invitation Modal -->
<div id="invite-modal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#1f293d]">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Undang Peserta ke Room</h3>
            <button type="button" onclick="closeInviteModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <div class="text-center py-4 bg-slate-50 dark:bg-[#0b0f19] rounded-xl border border-dashed border-slate-200 dark:border-[#1f293d]">
            <div class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest text-lg">{{ $room->room_id }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Bagikan Room ID ini kepada peserta lain</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tautan Undangan</label>
            <div class="flex items-center gap-2">
                <input type="text" id="invite-link-input" readonly value="{{ route('rooms.join', ['room_id' => $room->room_id]) }}"
                    class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#1f293d] bg-slate-100 dark:bg-[#151d30] text-xs font-mono text-slate-600 dark:text-slate-400">
                <button type="button" onclick="copyInviteLink()" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition">Salin</button>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" onclick="closeInviteModal()" class="w-full py-2 rounded-xl border border-slate-200 dark:border-[#1f293d] text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#151d30]">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const currentRoomId = "{{ $room->room_id }}";
    const currentUserId = "{{ $user->user_id }}";
    const currentUserName = "{{ $user->display_name }}";
    const currentUserAvatar = "{{ $user->avatar_color }}";

    function copyRoomId(id) {
        navigator.clipboard.writeText(id).then(() => alert('Room ID ' + id + ' disalin!'));
    }

    function toggleMembersDrawer() {
        const drawer = document.getElementById('members-drawer');
        drawer.classList.toggle('hidden');
    }

    function openInviteModal() {
        document.getElementById('invite-modal').classList.remove('hidden');
    }

    function closeInviteModal() {
        document.getElementById('invite-modal').classList.add('hidden');
    }

    function copyInviteLink() {
        const input = document.getElementById('invite-link-input');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => alert('Tautan undangan disalin!'));
    }

    function handleSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        if (!text) return;

        const duration = parseInt(document.getElementById('ephemeral_duration').value);

        // Append message to UI
        const list = document.getElementById('messages-list');
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        const bubble = document.createElement('div');
        bubble.className = 'flex items-start justify-end gap-2.5';
        bubble.innerHTML = `
            <div class="flex flex-col items-end max-w-[80%]">
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mb-0.5">
                    <span>${timeStr}</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Anda</span>
                </div>
                <div class="p-3 rounded-2xl rounded-tr-sm bg-blue-600 text-white text-xs leading-relaxed shadow-sm">
                    ${text}
                </div>
                <div class="flex items-center gap-1 text-[10px] text-slate-400 mt-1 font-mono">
                    <span class="text-blue-500 font-bold">✓✓ Delivered</span>
                    ${duration > 0 ? `<span>&bull; Ephemeral (${duration}s)</span>` : ''}
                </div>
            </div>
        `;

        list.appendChild(bubble);
        input.value = '';

        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;

        if (duration > 0) {
            setTimeout(() => {
                bubble.style.transition = 'opacity 0.5s';
                bubble.style.opacity = '0';
                setTimeout(() => bubble.remove(), 500);
            }, duration * 1000);
        }
    }

    function toggleRoomLock() {
        fetch(`/api/rooms/${currentRoomId}/lock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status lock');
            }
        });
    }

    function kickUser(targetUserId, targetName) {
        if (!confirm(`Keluarkan ${targetName} dari room?`)) return;

        fetch(`/api/rooms/${currentRoomId}/kick`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ target_user_id: targetUserId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal mengeluarkan peserta');
            }
        });
    }
</script>
@endpush
@endsection
