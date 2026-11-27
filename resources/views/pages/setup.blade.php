@extends('layouts.app')

@section('title', 'Setup Identitas — SecureLink')

@section('content')
<div class="max-w-lg mx-auto py-6 sm:py-10">
    <div class="sl-surface rounded-xl p-6 sm:p-8 space-y-6">
        <div class="text-center space-y-2">
            @php
                $initialColor = old('avatar_color', $user?->avatar_color ?? '#497173');
                $selectedIcon = old('avatar_icon', $user?->avatar_icon ?? 'initial');
                $characterMetadata = \App\Models\UserSession::$characterMetadata;
                $characterSvgs = \App\Models\UserSession::$avatarSvgs;
                $resolvedKey = \App\Models\UserSession::$legacyAliases[$selectedIcon] ?? $selectedIcon;
                $hasSvg = ($resolvedKey !== 'initial' && isset($characterSvgs[$resolvedKey]));
                $initialLetter = strtoupper(substr(old('display_name', $user?->display_name ?? 'A'), 0, 1) ?: 'U');
            @endphp
            <div id="avatar-preview" class="w-16 h-16 rounded-full text-white flex items-center justify-center mx-auto text-2xl font-bold uppercase p-3 overflow-hidden" style="background-color: {{ $initialColor }};">
                @if($hasSvg)
                    <div class="w-full h-full flex items-center justify-center">{!! $characterSvgs[$resolvedKey] !!}</div>
                @else
                    <span class="font-bold select-none leading-none">{{ $initialLetter }}</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-[var(--sl-text-primary)]">Identitas Sesi Pengguna</h1>
            <p class="text-xs text-[var(--sl-text-secondary)] max-w-sm mx-auto leading-relaxed">
                Tentukan nama tampilan dan avatar untuk mulai berkomunikasi melalui WebRTC DataChannel.
            </p>
        </div>

        <form action="{{ route('identity.setup.submit') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" id="avatar_icon" name="avatar_icon" value="{{ $selectedIcon }}">

            <div>
                <label for="display_name" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1.5">
                    Display Name <span class="text-[#EC643E]">*</span>
                </label>
                <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="50"
                    value="{{ old('display_name', $user?->display_name ?? '') }}"
                    placeholder="Contoh: Alice / Raka Pratama"
                    oninput="updateAvatarPreview()"
                    class="w-full px-3.5 py-2.5 rounded-lg sl-input text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-2">
                    Karakter Avatar
                </label>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @foreach($characterMetadata as $key => $meta)
                        @php
                            $isSvg = ($key !== 'initial' && isset($characterSvgs[$key]));
                            $isActive = ($selectedIcon === $key || (isset(\App\Models\UserSession::$legacyAliases[$selectedIcon]) && \App\Models\UserSession::$legacyAliases[$selectedIcon] === $key));
                        @endphp
                        <button type="button"
                            onclick="selectAvatarPreset('{{ $key }}')"
                            id="preset-btn-{{ $key }}"
                            class="avatar-preset-btn p-2 rounded-lg border text-center transition flex flex-col items-center justify-center gap-1 min-w-0 {{ $isActive ? 'border-[#497173] bg-[var(--sl-soft-teal)]' : 'border-[var(--sl-divider)] bg-[var(--sl-main-surface)] hover:border-[#71999A]' }}"
                            title="{{ $meta['desc'] }}">
                            <div class="w-6 h-6 flex items-center justify-center text-[var(--sl-primary-teal)] shrink-0">
                                @if($isSvg)
                                    {!! $characterSvgs[$key] !!}
                                @else
                                    <span class="text-[11px] font-bold bg-[var(--sl-white-surface)] px-1.5 py-0.5 rounded text-[var(--sl-text-primary)]">Aa</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-medium text-[var(--sl-text-primary)] truncate max-w-full">{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-2">
                    Warna Avatar
                </label>
                <div class="flex items-center flex-wrap gap-2.5">
                    @php
                        $colors = ['#497173', '#71999A', '#EC643E', '#3B8A72', '#20292B', '#2F5D62', '#5E8B7E'];
                        $selectedColor = $initialColor;
                    @endphp
                    @foreach($colors as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $selectedColor === $c ? 'checked' : '' }} onchange="updateAvatarColor('{{ $c }}')">
                            <div class="w-7 h-7 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-[#497173] transition" style="background-color: {{ $c }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="user_id" class="block text-xs font-semibold text-[var(--sl-text-primary)] mb-1.5">
                    User ID Sesi (Otomatis)
                </label>
                <input type="text" id="user_id" name="user_id" readonly
                    value="{{ old('user_id', $suggestedUserId) }}"
                    class="w-full px-3.5 py-2 rounded-lg border border-[var(--sl-divider)] bg-[var(--sl-main-surface)] text-xs font-mono text-[var(--sl-text-secondary)] cursor-not-allowed">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-lg sl-btn-coral font-semibold text-sm">
                Simpan Identitas &amp; Lanjutkan
            </button>
        </form>
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
        document.querySelectorAll('.avatar-preset-btn').forEach(btn => {
            btn.classList.remove('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
            btn.classList.add('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
        });
        const selectedBtn = document.getElementById('preset-btn-' + key);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-[var(--sl-divider)]', 'bg-[var(--sl-main-surface)]');
            selectedBtn.classList.add('border-[#497173]', 'bg-[var(--sl-soft-teal)]');
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
            preview.innerHTML = '<span class="font-bold select-none leading-none">' + letter + '</span>';
        }
    }

    function updateAvatarColor(color) {
        currentColor = color;
        document.getElementById('avatar-preview').style.backgroundColor = color;
    }
</script>
@endpush
@endsection
