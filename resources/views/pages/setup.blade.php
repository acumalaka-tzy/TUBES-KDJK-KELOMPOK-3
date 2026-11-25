@extends('layouts.app')

@section('title', 'Setup Identitas — SecureLink')

@section('content')
<div class="max-w-xl mx-auto py-6 sm:py-10">
    <div class="glass-card rounded-3xl p-6 sm:p-9 shadow-lg relative overflow-hidden">
        <!-- Top decorative gradient line (WhatsApp palette) -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#008069] via-[#00A884] to-[#25D366]"></div>

        <div class="text-center mb-6">
            @php
                $initialColor = old('avatar_color', $user?->avatar_color ?? '#008069');
                $selectedIcon = old('avatar_icon', $user?->avatar_icon ?? 'initial');
                $characterMetadata = \App\Models\UserSession::$characterMetadata;
                $characterSvgs = \App\Models\UserSession::$avatarSvgs;
                $resolvedKey = \App\Models\UserSession::$legacyAliases[$selectedIcon] ?? $selectedIcon;
                $hasSvg = ($resolvedKey !== 'initial' && isset($characterSvgs[$resolvedKey]));
                $initialLetter = strtoupper(substr(old('display_name', $user?->display_name ?? 'A'), 0, 1) ?: 'U');
            @endphp
            <div id="avatar-preview" class="w-20 h-20 rounded-3xl text-white flex items-center justify-center mx-auto mb-3 text-3xl font-extrabold shadow-lg shadow-emerald-500/25 transition-all duration-300 uppercase transform hover:scale-105 p-3 overflow-hidden" style="background-color: {{ $initialColor }};">
                @if($hasSvg)
                    <div class="w-full h-full flex items-center justify-center">
                        {!! $characterSvgs[$resolvedKey] !!}
                    </div>
                @else
                    <span class="font-extrabold select-none leading-none">{{ $initialLetter }}</span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#111B21] dark:text-[#E9EDEF]">Identitas Sesi Pengguna</h1>
            <p class="text-xs text-[#667781] dark:text-[#8696A0] mt-1.5 leading-relaxed max-w-sm mx-auto">
                Pilih karakter avatar vektor dan nama tampilan Anda untuk berkomunikasi di ruang obrolan P2P privat.
            </p>
        </div>

        <form action="{{ route('identity.setup.submit') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" id="avatar_icon" name="avatar_icon" value="{{ $selectedIcon }}">

            <!-- Display Name -->
            <div>
                <label for="display_name" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1.5">
                    Display Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="50"
                    value="{{ old('display_name', $user?->display_name ?? '') }}"
                    placeholder="Contoh: Alice Crypt / Ksatria P2P"
                    oninput="updateAvatarPreview()"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 text-sm text-[#111B21] dark:text-[#E9EDEF] placeholder-[#667781]/60 dark:placeholder-[#8696A0]/40 focus:outline-none focus:ring-2 focus:ring-[#008069] dark:focus:ring-[#00A884] transition">
            </div>

            <!-- Avatar Preset Selection Grid (Vector Characters, Non-Emoji) -->
            <div>
                <label class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-2">
                    Pilihan Karakter Avatar (Vektor Grafis)
                </label>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach($characterMetadata as $key => $meta)
                        @php
                            $isSvg = ($key !== 'initial' && isset($characterSvgs[$key]));
                            $isActive = ($selectedIcon === $key || (isset(\App\Models\UserSession::$legacyAliases[$selectedIcon]) && \App\Models\UserSession::$legacyAliases[$selectedIcon] === $key));
                        @endphp
                        <button type="button"
                            onclick="selectAvatarPreset('{{ $key }}')"
                            id="preset-btn-{{ $key }}"
                            class="avatar-preset-btn p-2 sm:p-2.5 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-1 sm:gap-1.5 min-w-0 {{ $isActive ? 'border-[#008069] dark:border-[#00A884] bg-emerald-50 dark:bg-emerald-950/40 ring-2 ring-[#008069] dark:ring-[#00A884]' : 'border-[#E9EDEF] dark:border-[#222D34] bg-white/60 dark:bg-[#202C33]/60 hover:border-[#008069]/40' }}"
                            title="{{ $meta['desc'] }}">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center text-[#008069] dark:text-[#25D366] shrink-0">
                                @if($isSvg)
                                    {!! $characterSvgs[$key] !!}
                                @else
                                    <span class="text-[11px] sm:text-xs font-black tracking-tight bg-[#E9EDEF] dark:bg-[#202C33] px-1 sm:px-1.5 py-0.5 rounded text-[#111B21] dark:text-[#E9EDEF]">Aa</span>
                                @endif
                            </div>
                            <span class="text-[9px] sm:text-[10px] font-semibold text-[#111B21] dark:text-[#E9EDEF] truncate max-w-full">{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- WhatsApp Color Swatches -->
            <div>
                <label class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-2">
                    Warna Latar Avatar (Palet WhatsApp)
                </label>
                <div class="flex items-center justify-between flex-wrap gap-2.5 pt-1">
                    @php
                        $colors = ['#008069', '#25D366', '#128C7E', '#00A884', '#075E54', '#34B7F1', '#1F2C34'];
                        $selectedColor = $initialColor;
                    @endphp
                    @foreach($colors as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $selectedColor === $c ? 'checked' : '' }} onchange="updateAvatarColor('{{ $c }}')">
                            <div class="w-8 h-8 rounded-full peer-checked:ring-3 peer-checked:ring-offset-2 peer-checked:ring-[#008069] dark:peer-checked:ring-[#00A884] dark:peer-checked:ring-offset-[#111B21] transition-all duration-200 hover:scale-110 shadow-sm" style="background-color: {{ $c }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- User ID Unik (Readonly) -->
            <div>
                <label for="user_id" class="block text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] mb-1.5">
                    User ID Unik Sesi (Otomatis)
                </label>
                <input type="text" id="user_id" name="user_id" readonly
                    value="{{ old('user_id', $suggestedUserId) }}"
                    class="w-full px-4 py-2.5 rounded-2xl border border-[#E9EDEF] dark:border-[#222D34] bg-[#F0F2F5]/60 dark:bg-[#202C33]/60 text-xs font-mono text-[#667781] dark:text-[#8696A0] cursor-not-allowed">
                <p class="text-[11px] text-[#667781] dark:text-[#8696A0] mt-1">
                    Dibuat otomatis oleh sistem untuk pengalamatan sesi dan negosiasi WebRTC P2P.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-[#008069] to-[#00A884] hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-emerald-600/25 transition-all hover:-translate-y-0.5">
                    Simpan Identitas &amp; Lanjutkan
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-[#E9EDEF] dark:border-[#222D34] text-center">
            <p class="text-[11px] text-[#667781] dark:text-[#8696A0] leading-relaxed">
                <strong>Catatan Keamanan:</strong> Sesuai PRD Section 3.1, identitas sesi ini tidak bertindak sebagai autentikasi akun dunia nyata. Seluruh otorisasi room diverifikasi secara independen oleh backend.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const avatarSvgs = @json(\App\Models\UserSession::$avatarSvgs);
    let currentIcon = "{{ $selectedIcon }}";
    let currentColor = "{{ $selectedColor }}";

    function selectAvatarPreset(key) {
        currentIcon = key;
        document.getElementById('avatar_icon').value = key;

        // Update preset buttons styling
        document.querySelectorAll('.avatar-preset-btn').forEach(btn => {
            btn.classList.remove('border-[#008069]', 'dark:border-[#00A884]', 'bg-emerald-50', 'dark:bg-emerald-950/40', 'ring-2', 'ring-[#008069]', 'dark:ring-[#00A884]');
            btn.classList.add('border-[#E9EDEF]', 'dark:border-[#222D34]', 'bg-white/60', 'dark:bg-[#202C33]/60');
        });

        const selectedBtn = document.getElementById('preset-btn-' + key);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-[#E9EDEF]', 'dark:border-[#222D34]', 'bg-white/60', 'dark:bg-[#202C33]/60');
            selectedBtn.classList.add('border-[#008069]', 'dark:border-[#00A884]', 'bg-emerald-50', 'dark:bg-emerald-950/40', 'ring-2', 'ring-[#008069]', 'dark:ring-[#00A884]');
        }

        updateAvatarPreview();
    }

    function updateAvatarPreview() {
        const preview = document.getElementById('avatar-preview');
        const name = document.getElementById('display_name').value.trim();
        if (currentIcon !== 'initial' && avatarSvgs[currentIcon]) {
            preview.innerHTML = '<div class="w-full h-full flex items-center justify-center p-0.5">' + avatarSvgs[currentIcon] + '</div>';
        } else {
            const letter = name ? name.charAt(0).toUpperCase() : 'U';
            preview.innerHTML = '<span class="font-extrabold select-none leading-none">' + letter + '</span>';
        }
    }

    function updateAvatarColor(color) {
        currentColor = color;
        const preview = document.getElementById('avatar-preview');
        preview.style.backgroundColor = color;
    }
</script>
@endpush
@endsection
