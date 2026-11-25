@extends('layouts.app')

@section('title', 'Setup Identitas — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-10">
    <div class="bg-white dark:bg-[#1B2230] border border-[#E3E7EF] dark:border-[#344052] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="text-center mb-6">
            <div id="avatar-preview" class="w-16 h-16 rounded-2xl text-white flex items-center justify-center mx-auto mb-3 text-2xl font-bold shadow-md shadow-blue-500/10 transition-colors uppercase" style="background-color: {{ old('avatar_color', $user?->avatar_color ?? '#315CE8') }}">
                {{ substr(old('display_name', $user?->display_name ?? 'A'), 0, 1) ?: 'U' }}
            </div>
            <h1 class="text-xl font-extrabold text-[#182033] dark:text-[#F1F4FA]">Identitas Sesi Pengguna</h1>
            <p class="text-xs text-[#667085] dark:text-[#A8B2C3] mt-1 leading-relaxed">
                Tentukan nama tampilan Anda untuk berkomunikasi di ruang obrolan P2P.
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
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-white dark:bg-[#151A24] text-sm text-[#182033] dark:text-[#F1F4FA] placeholder-[#667085] dark:placeholder-[#A8B2C3]/60 focus:outline-none focus:ring-2 focus:ring-[#315CE8] dark:focus:ring-[#7895FF]">
            </div>

            <div>
                <label for="user_id" class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    User ID Unik
                </label>
                <input type="text" id="user_id" name="user_id" readonly
                    value="{{ old('user_id', $suggestedUserId) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E3E7EF] dark:border-[#344052] bg-[#EEF1F7]/60 dark:bg-[#222B3A] text-xs font-mono text-[#667085] dark:text-[#A8B2C3] cursor-not-allowed">
                <p class="text-[11px] text-[#667085] dark:text-[#A8B2C3] mt-1">
                    Dibuat otomatis oleh sistem untuk pengalamatan sesi dan negosiasi WebRTC.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#182033] dark:text-[#F1F4FA] mb-1.5">
                    Pilihan Warna Avatar
                </label>
                <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
                    @php
                        $colors = ['#315CE8', '#7657D6', '#16845B', '#B7791F', '#D14343', '#0891B2', '#4F46E5'];
                        $selectedColor = old('avatar_color', $user?->avatar_color ?? $colors[0]);
                    @endphp
                    @foreach($colors as $c)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $c }}" class="sr-only peer" {{ $selectedColor === $c ? 'checked' : '' }} onchange="updateAvatarColor('{{ $c }}')">
                            <div class="w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-[#315CE8] dark:peer-checked:ring-[#7895FF] dark:peer-checked:ring-offset-[#1B2230] transition-all hover:scale-105 shadow-2xs" style="background-color: {{ $c }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#315CE8] hover:bg-[#254BC5] text-white font-semibold text-sm shadow-sm transition">
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
        document.getElementById('avatar-preview').style.backgroundColor = color;
    }
</script>
@endpush
@endsection
