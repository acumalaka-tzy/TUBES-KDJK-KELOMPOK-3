@extends('layouts.app')

@section('title', 'Setup Identitas — SecureLink')

@section('content')
<div class="max-w-md mx-auto py-10">
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1f293d] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Identitas Sesi Anda</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Tentukan nama tampilan Anda. Nama ini hanya berlaku pada sesi obrolan saat ini.
            </p>
        </div>

        <form action="{{ route('identity.setup.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="display_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Display Name *</label>
                <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="50"
                    value="{{ old('display_name', $user?->display_name ?? '') }}"
                    placeholder="Contoh: Alice Crypt"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#1f293d] bg-white dark:bg-[#0b0f19] text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="user_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">User ID Unik</label>
                <input type="text" id="user_id" name="user_id" readonly
                    value="{{ old('user_id', $suggestedUserId) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-[#1f293d] bg-slate-100 dark:bg-[#151d30] text-xs font-mono text-slate-600 dark:text-slate-400 cursor-not-allowed">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                    Dibuat otomatis oleh sistem untuk pengalamatan sesi P2P.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Warna Avatar</label>
                <div class="flex items-center gap-2">
                    @php
                        $colors = ['#2563eb', '#7c3aed', '#059669', '#d97706', '#e11d48', '#0891b2', '#4f46e5'];
                        $selectedColor = old('avatar_color', $user?->avatar_color ?? $colors[0]);
                    @endphp
                    @foreach($colors as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar_color" value="{{ $color }}" class="sr-only peer" {{ $selectedColor === $color ? 'checked' : '' }}>
                            <div class="w-7 h-7 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-blue-500 transition-all" style="background-color: {{ $color }};"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
                    Simpan &amp; Lanjutkan
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-[#1f293d] text-center">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">
                <strong>Catatan Keamanan:</strong> Sesuai PRD, identitas ini adalah identitas sesi browsing dan bukan autentikasi permanen akun dunia nyata.
            </p>
        </div>
    </div>
</div>
@endsection
