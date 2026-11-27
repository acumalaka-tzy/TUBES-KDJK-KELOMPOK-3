@php
    $isChatsActive = request()->routeIs('dashboard') || request()->routeIs('rooms.show');
    $isSettingsActive = request()->routeIs('settings');
@endphp

<aside aria-label="Primary Navigation" class="w-14 sm:w-16 bg-[var(--sl-nav-bg)] text-white flex flex-col items-center justify-between py-3.5 shrink-0 select-none z-30">
    <!-- Top: Brand & Core Navigation -->
    <div class="flex flex-col items-center gap-2 w-full px-2">
        <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center mb-2 hover:bg-white/25 transition" title="SecureLink">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </a>

        <!-- Chats -->
        <a href="{{ route('dashboard') }}"
           onclick="if (typeof window.setNavFilter === 'function') { window.setNavFilter('all'); }"
           class="group relative w-10 h-10 rounded-xl flex items-center justify-center transition {{ $isChatsActive ? 'bg-white/20 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
           title="Chats">
            @if($isChatsActive)
                <span class="absolute -left-2 w-1 h-5 rounded-r bg-[var(--sl-accent-coral)]"></span>
            @endif
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Chats</span>
        </a>

        <!-- Contacts -->
        <button type="button"
           onclick="if (typeof window.setNavFilter === 'function') { window.setNavFilter('private'); } else { window.location.href='{{ route('dashboard', ['filter' => 'private']) }}'; }"
           class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-white/75 hover:bg-white/10 hover:text-white transition"
           title="Contacts">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Contacts</span>
        </button>

        <!-- Archived -->
        <button type="button"
           onclick="if (typeof window.setNavFilter === 'function') { window.setNavFilter('archived'); } else { window.location.href='{{ route('dashboard', ['filter' => 'archived']) }}'; }"
           class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-white/75 hover:bg-white/10 hover:text-white transition"
           title="Archived">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>
            </svg>
            <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Archived</span>
        </button>

        <!-- Settings -->
        <a href="{{ route('settings') }}"
           class="group relative w-10 h-10 rounded-xl flex items-center justify-center transition {{ $isSettingsActive ? 'bg-white/20 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
           title="Settings">
            @if($isSettingsActive)
                <span class="absolute -left-2 w-1 h-5 rounded-r bg-[var(--sl-accent-coral)]"></span>
            @endif
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Settings</span>
        </a>
    </div>

    <!-- Bottom: Theme, Profile, Logout -->
    <div class="flex flex-col items-center gap-2 w-full px-2">
        <button type="button" id="nav-theme-btn" onclick="cycleTheme(this)" class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-white/75 hover:bg-white/10 hover:text-white transition-all duration-300" title="Ganti Tema">
            <svg id="nav-theme-sun" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
            </svg>
            <svg id="nav-theme-moon" class="w-5 h-5 hidden transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Ganti Tema</span>
        </button>

        @if(isset($user) && $user)
            <a href="{{ route('settings', ['section' => 'account']) }}" class="group relative w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-0.5 ring-2 ring-white/25 hover:ring-white/60 transition" style="background-color: {{ $user->avatar_color }}" title="Profile">
                {!! $user->avatar_html !!}
                <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Profile</span>
            </a>
        @endif

        <form action="{{ route('identity.logout.web') }}" method="POST" onsubmit="event.preventDefault(); window.slConfirmSignOut(this);" class="w-full flex justify-center">
            @csrf
            <button type="submit" class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-white/75 hover:bg-[#EC643E]/25 hover:text-white transition" title="Keluar Sesi (Logout)" aria-label="Keluar Sesi (Logout)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span class="pointer-events-none hidden md:block opacity-0 group-hover:opacity-100 transition absolute left-13 px-2 py-1 rounded bg-[#20292B] text-white text-[11px] font-medium whitespace-nowrap z-50">Keluar Sesi (Logout)</span>
            </button>
        </form>
    </div>
</aside>
