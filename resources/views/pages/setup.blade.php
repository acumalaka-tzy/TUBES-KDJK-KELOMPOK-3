@extends('layouts.app')

@section('title', 'Setup Identitas — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-12">
    <div class="glass-card rounded-3xl p-7 sm:p-9 shadow-lg relative overflow-hidden">
        <!-- Top decorative gradient line -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#315CE8] via-[#7657D6] to-[#A58AE8]"></div>

        <div class="text-center mb-7">
            <div id="avatar-preview" class="w-18 h-18 rounded-3xl text-white flex items-center justify-center mx-auto mb-3 text-3xl font-extrabold shadow-lg shadow-blue-500/25 transition-all duration-300 uppercase transform hover:scale-105" style="background-color: {{ old('avatar_color', $user?->avatar_color ?? '#315CE8') }}">
                {{ substr(old('display_name', $user?->display_name ?? 'A'), 0, 1) ?: 'U' }}
            </div>
            <h1 class="text-xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Identitas Sesi Pengguna</h1>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1.5 leading-relaxed">
                Tentukan nama tampilan Anda untuk berkomunikasi di ruang obrolan P2P privat.
            </p>
        </div>

        <form action="{{ route('identity.setup.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="display_name" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    Display Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="50"
                    value="{{ old('display_name', $user?->display_name ?? '') }}"
                    placeholder="Contoh: Alice Crypt"
                    oninput="updateAvatarPreview()"
                    class="w-full px-4 py-3 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-white/80 dark:bg-[#151A24]/80 text-sm text-[#182033] dark:text-[#F1F4FA] placeholder-[#667085]/60 dark:placeholder-[#A8B2C3]/40 focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF] transition">
            </div>

            <div>
                <label for="user_id" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    User ID Unik (Otomatis)
                </label>
                <input type="text" id="user_id" name="user_id" readonly
                    value="{{ old('user_id', $suggestedUserId) }}"
                    class="w-full px-4 py-2.5 rounded-2xl border border-[#E3E7EF] dark:border-[#344052] bg-[#EEF1F7]/60 dark:bg-[#222B3A]/60 text-xs font-mono text-[#667085] dark:text-[#A8B2C3] cursor-not-allowed">
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    Dibuat otomatis oleh sistem untuk pengalamatan sesi dan negosiasi WebRTC.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-2">
                    Pilihan Warna Avatar
                </label>
                <div class="flex items-center justify-between flex-wrap gap-2.5 pt-1">
                    @php
                        $colors = ['#315CE8', '#7657D6', '#16845B', '#B7791F', '#D14343', '#0891B2', '#4F46E5'];
                        $selectedColor = old('avatar_color', $user?->avatar_color ?? $colors[0]);
                    @endphp
                    @foreach($colors as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $selectedColor === $c ? 'checked' : '' }} onchange="updateAvatarColor('{{ $c }}')">
                            <div class="w-8 h-8 rounded-full peer-checked:ring-3 peer-checked:ring-offset-2 peer-checked:ring-[#315CE8] dark:peer-checked:ring-[#7895FF] dark:peer-checked:ring-offset-[#1B2230] transition-all duration-200 hover:scale-110 shadow-sm" style="background-color: {{ $c }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-[#315CE8] to-[#254BC5] hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-blue-500/25 transition-all hover:-translate-y-0.5">
                    Simpan Identitas &amp; Lanjutkan
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-[#E3E7EF] dark:border-[#344052] text-center">
            <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] leading-relaxed">
                <strong>Catatan Keamanan:</strong> Sesuai PRD Section 3.1, identitas sesi ini tidak bertindak sebagai autentikasi akun dunia nyata. Seluruh otorisasi room diverifikasi secara independen oleh backend.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentColor = "{{ $selectedColor }}";

    function updateAvatarPreview() {
        const name = document.getElementById('display_name').value.trim();
        const preview = document.getElementById('avatar-preview');
        preview.textContent = name ? name.charAt(0).toUpperCase() : 'U';
    }

    function updateAvatarColor(color) {
        currentColor = color;
        const preview = document.getElementById('avatar-preview');
        preview.style.backgroundColor = color;
    }
</script>
@endpush
@endsection
