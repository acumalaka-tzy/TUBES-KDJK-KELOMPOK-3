<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecureLink — Private P2P Messaging')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Immediate Theme Initialization (Prevents FOUC) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('securelink_theme') || 'system';
            function applyTheme(theme) {
                const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            }
            applyTheme(savedTheme);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if ((localStorage.getItem('securelink_theme') || 'system') === 'system') {
                    applyTheme('system');
                }
            });
        })();
    </script>

    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans antialiased bg-[var(--sl-page-bg)] text-[var(--sl-text-primary)] selection:bg-[#497173] selection:text-white min-h-screen">

    @hasSection('app_shell')
        @yield('app_shell')
    @else
        <!-- Clean Minimal Top Header -->
        <header class="sticky top-0 z-40 w-full bg-[var(--sl-white-surface)] border-b border-[var(--sl-divider)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[#497173] flex items-center justify-center text-white shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-base tracking-tight text-[var(--sl-text-primary)] truncate">SecureLink</span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-1">
                    @if(session()->has('securelink_user_id'))
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]' }}">
                            Chats
                        </a>
                        <a href="{{ route('rooms.create') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('rooms.create') ? 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]' }}">
                            Buat Room
                        </a>
                        <a href="{{ route('rooms.join') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('rooms.join') ? 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]' }}">
                            Gabung Room
                        </a>
                    @endif
                    <a href="{{ route('demo') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('demo') ? 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]' }}">
                        Demonstrasi
                    </a>
                    <a href="{{ route('settings') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('settings') ? 'bg-[var(--sl-soft-teal)] text-[var(--sl-primary-teal)]' : 'text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]' }}">
                        Settings
                    </a>
                </nav>

                <!-- Right Controls -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="cycleTheme()" class="p-2 rounded-lg text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition" title="Ganti Tema">
                        <span id="theme-icon-light" class="hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        <span id="theme-icon-dark" class="hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </span>
                        <span id="theme-icon-system" class="hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                    </button>

                    @if(isset($user) && $user)
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 pl-2 border-l border-[var(--sl-divider)]">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase overflow-hidden p-0.5 shrink-0" style="background-color: {{ $user->avatar_color }}">
                                {!! $user->avatar_html !!}
                            </div>
                            <span class="hidden sm:inline text-xs font-semibold text-[var(--sl-text-primary)]">{{ $user->display_name }}</span>
                        </a>
                    @else
                        <a href="{{ route('identity.setup') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg sl-btn-primary">
                            Open SecureLink
                        </a>
                    @endif

                    <button type="button" onclick="toggleMobileNav()" class="md:hidden p-2 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-main-surface)]" aria-label="Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav Drawer -->
            <div id="mobile-nav" class="hidden md:hidden border-t border-[var(--sl-divider)] bg-[var(--sl-white-surface)] px-4 py-2.5 space-y-1">
                @if(session()->has('securelink_user_id'))
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Chats</a>
                    <a href="{{ route('rooms.create') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Buat Room</a>
                    <a href="{{ route('rooms.join') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Gabung Room</a>
                @else
                    <a href="{{ route('identity.setup') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-primary-teal)] hover:bg-[var(--sl-main-surface)]">Setup Identitas</a>
                @endif
                <a href="{{ route('demo') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Demonstrasi P2P</a>
                <a href="{{ route('settings') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Settings</a>
                <a href="{{ route('tailscale.guide') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Panduan Tailscale</a>
                @if(session()->has('securelink_user_id'))
                    <form action="{{ route('identity.logout.web') }}" method="POST" class="pt-1 border-t border-[var(--sl-divider)]">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold text-[#EC643E] hover:bg-[var(--sl-main-surface)]">Keluar Sesi</button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 mt-3 w-full">
            @if(session('success'))
                <div class="mb-3 px-4 py-2.5 rounded-lg bg-[var(--sl-soft-teal)] border border-[var(--sl-secondary-teal)]/40 text-[var(--sl-primary-teal)] text-xs font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="font-bold ml-3">&times;</button>
                </div>
            @endif
            @if(session('info'))
                <div class="mb-3 px-4 py-2.5 rounded-lg bg-[var(--sl-white-surface)] border border-[var(--sl-divider)] text-[var(--sl-text-primary)] text-xs font-medium flex items-center justify-between">
                    <span>{{ session('info') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="font-bold ml-3">&times;</button>
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-3 px-4 py-2.5 rounded-lg bg-[#EC643E]/10 border border-[#EC643E]/40 text-[#EC643E] text-xs font-medium flex items-center justify-between">
                    <span>{{ session('warning') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="font-bold ml-3">&times;</button>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-3 px-4 py-2.5 rounded-lg bg-[#EC643E]/10 border border-[#EC643E]/40 text-[#EC643E] text-xs font-medium space-y-1">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 w-full py-5">
            @yield('content')
        </main>

        <!-- Simple Footer -->
        <footer class="mt-auto border-t border-[var(--sl-divider)] bg-[var(--sl-white-surface)] py-5 text-xs text-[var(--sl-text-secondary)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[var(--sl-text-primary)]">SecureLink</span>
                    <span>&middot;</span>
                    <span>Peer-to-Peer Encrypted Messaging</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('demo') }}" class="hover:text-[var(--sl-primary-teal)] transition">Demonstrasi</a>
                    <a href="{{ route('settings') }}" class="hover:text-[var(--sl-primary-teal)] transition">Diagnostik</a>
                    <a href="{{ route('tailscale.guide') }}" class="hover:text-[var(--sl-primary-teal)] transition">Tailscale VPN</a>
                </div>
            </div>
        </footer>
    @endif

    <script>
        function updateThemeIcons(mode) {
            const lightIcon = document.getElementById('theme-icon-light');
            const darkIcon = document.getElementById('theme-icon-dark');
            const sysIcon = document.getElementById('theme-icon-system');
            if (!lightIcon || !darkIcon || !sysIcon) return;

            lightIcon.classList.add('hidden');
            darkIcon.classList.add('hidden');
            sysIcon.classList.add('hidden');

            if (mode === 'light') lightIcon.classList.remove('hidden');
            else if (mode === 'dark') darkIcon.classList.remove('hidden');
            else sysIcon.classList.remove('hidden');
        }

        function setThemeMode(mode) {
            localStorage.setItem('securelink_theme', mode);
            const isDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
            updateThemeIcons(mode);
        }

        function cycleTheme() {
            const current = localStorage.getItem('securelink_theme') || 'system';
            const modes = ['system', 'light', 'dark'];
            setThemeMode(modes[(modes.indexOf(current) + 1) % modes.length]);
        }

        function toggleMobileNav() {
            const el = document.getElementById('mobile-nav');
            if (el) el.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcons(localStorage.getItem('securelink_theme') || 'system');
        });
    </script>

    @stack('scripts')
</body>
</html>
