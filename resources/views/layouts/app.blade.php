<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecureLink — Secure Peer-to-Peer Messaging')</title>

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <!-- Tailwind CSS with PRD v2.1 Color Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // WhatsApp Color Tokens
                        app: {
                            light: '#EFEAE2',
                            dark: '#0B141A'
                        },
                        sidebar: {
                            light: '#FFFFFF',
                            dark: '#111B21'
                        },
                        surface: {
                            light: '#FFFFFF',
                            dark: '#111B21',
                            muted: {
                                light: '#F0F2F5',
                                dark: '#202C33'
                            }
                        },
                        brand: {
                            primary: '#008069',
                            hover: '#00A884',
                            darkPrimary: '#00A884',
                            darkHover: '#25D366',
                            accent: '#25D366',
                            darkAccent: '#25D366'
                        },
                        content: {
                            primary: {
                                light: '#111B21',
                                dark: '#E9EDEF'
                            },
                            secondary: {
                                light: '#667781',
                                dark: '#8696A0'
                            }
                        },
                        divider: {
                            light: '#E9EDEF',
                            dark: '#222D34'
                        },
                        status: {
                            success: {
                                light: '#25D366',
                                dark: '#00A884'
                            },
                            warning: {
                                light: '#FFB703',
                                dark: '#E7B45B'
                            },
                            danger: {
                                light: '#EA0038',
                                dark: '#F15C6D'
                            }
                        }
                    },
                    fontFamily: {
                        sans: ['Instrument Sans', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-app: #EFEAE2;
            --bg-sidebar: #FFFFFF;
            --bg-surface: #FFFFFF;
            --bg-surface-muted: #F0F2F5;
            --color-primary: #008069;
            --color-primary-hover: #00A884;
            --color-accent: #25D366;
            --color-text-primary: #111B21;
            --color-text-secondary: #667781;
            --color-border: #E9EDEF;
            --color-success: #25D366;
            --color-warning: #FFB703;
            --color-danger: #EA0038;
        }

        html.dark {
            color-scheme: dark;
            --bg-app: #0B141A;
            --bg-sidebar: #111B21;
            --bg-surface: #111B21;
            --bg-surface-muted: #202C33;
            --color-primary: #00A884;
            --color-primary-hover: #25D366;
            --color-accent: #25D366;
            --color-text-primary: #E9EDEF;
            --color-text-secondary: #8696A0;
            --color-border: #222D34;
            --color-success: #00A884;
            --color-warning: #E7B45B;
            --color-danger: #F15C6D;
        }

        body {
            background-color: var(--bg-app);
            color: var(--color-text-primary);
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        /* Glass / border utilities matching blue-charcoal aesthetic */
        .theme-surface {
            background-color: var(--bg-surface);
            border-color: var(--color-border);
        }
        .theme-surface-muted {
            background-color: var(--bg-surface-muted);
        }
        .theme-border {
            border-color: var(--color-border);
        }
    </style>

    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans antialiased selection:bg-blue-600 selection:text-white relative min-h-screen">

    <!-- Ambient Gradient Background Layer -->
    <div class="ambient-bg">
        <div class="ambient-glow-1"></div>
        <div class="ambient-glow-2"></div>
    </div>

    <!-- Immediate Theme Initialization Script (Prevents flash of unstyled content) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('securelink_theme') || 'system';
            function applyTheme(theme) {
                const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            applyTheme(savedTheme);

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if ((localStorage.getItem('securelink_theme') || 'system') === 'system') {
                    applyTheme('system');
                }
            });
        })();
    </script>

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 w-full glass-nav shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 font-bold text-lg tracking-tight hover:opacity-90 transition">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#008069] to-[#25D366] flex items-center justify-center text-white shadow-sm shadow-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[#111B21] dark:text-[#E9EDEF] font-extrabold text-base leading-tight">SecureLink</span>
                        <span class="text-[10px] text-[#008069] dark:text-[#25D366] font-semibold tracking-wider uppercase">P2P Network Lab</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1">
                @if(session()->has('securelink_user_id'))
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] {{ request()->routeIs('dashboard') ? 'text-[#008069] dark:text-[#25D366] bg-emerald-50 dark:bg-emerald-950/40 font-bold' : 'text-[#667781] dark:text-[#8696A0]' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('rooms.create') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] {{ request()->routeIs('rooms.create') ? 'text-[#008069] dark:text-[#25D366] bg-emerald-50 dark:bg-emerald-950/40 font-bold' : 'text-[#667781] dark:text-[#8696A0]' }}">
                        Buat Room
                    </a>
                    <a href="{{ route('rooms.join') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] {{ request()->routeIs('rooms.join') ? 'text-[#008069] dark:text-[#25D366] bg-emerald-50 dark:bg-emerald-950/40 font-bold' : 'text-[#667781] dark:text-[#8696A0]' }}">
                        Gabung Room
                    </a>
                @endif
                <a href="{{ route('demo') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] {{ request()->routeIs('demo') ? 'text-[#008069] dark:text-[#25D366] bg-emerald-50 dark:bg-emerald-950/40 font-bold' : 'text-[#667781] dark:text-[#8696A0]' }}">
                    Demonstrasi P2P
                </a>
                <a href="{{ route('settings') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] {{ request()->routeIs('settings') ? 'text-[#008069] dark:text-[#25D366] bg-emerald-50 dark:bg-emerald-950/40 font-bold' : 'text-[#667781] dark:text-[#8696A0]' }}">
                    Settings
                </a>
            </nav>

            <!-- User Status, Theme Switcher & Mobile Menu Button -->
            <div class="flex items-center gap-2.5">
                <!-- Theme Switcher Button -->
                <button type="button" onclick="cycleTheme()" class="p-2 rounded-xl text-[#667781] hover:text-[#111B21] dark:text-[#8696A0] dark:hover:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33] transition" title="Ganti Tema (Light/Dark/System)">
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
                    <div class="flex items-center gap-2 pl-2 border-l border-[#E9EDEF] dark:border-[#222D34]">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase shadow-sm overflow-hidden p-1 shrink-0" style="background-color: {{ $user->avatar_color }}">
                            {!! $user->avatar_html !!}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] leading-none">{{ $user->display_name }}</span>
                            <span class="text-[10px] text-emerald-600 dark:text-[#25D366] font-mono mt-0.5">● Online</span>
                        </div>
                    </div>
                @else
                    <a href="{{ route('identity.setup') }}" class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-[#008069] hover:bg-[#00A884] text-white shadow-sm transition">
                        Setup Identitas
                    </a>
                @endif

                <!-- Mobile Menu Button -->
                <button type="button" onclick="toggleMobileNav()" class="md:hidden p-2 rounded-xl text-[#667085] dark:text-[#A8B2C3] hover:bg-[#EEF1F7] dark:hover:bg-[#2A3546] transition" aria-label="Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-nav" class="hidden md:hidden border-t border-[#E9EDEF] dark:border-[#222D34] bg-white dark:bg-[#111B21] px-4 py-3 space-y-1">
            @if(session()->has('securelink_user_id'))
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                    Dashboard
                </a>
                <a href="{{ route('rooms.create') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                    Buat Room Baru
                </a>
                <a href="{{ route('rooms.join') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                    Gabung Room
                </a>
            @else
                <a href="{{ route('identity.setup') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#008069] dark:text-[#25D366] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                    Setup Identitas
                </a>
            @endif
            <a href="{{ route('demo') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                Demonstrasi P2P &amp; Broadcast
            </a>
            <a href="{{ route('settings') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                Settings &amp; Diagnostik
            </a>
            <a href="{{ route('tailscale.guide') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[#111B21] dark:text-[#E9EDEF] hover:bg-[#F0F2F5] dark:hover:bg-[#202C33]">
                Panduan VPN Tailscale
            </a>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-blue-800 dark:text-blue-300 text-xs font-medium flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-medium flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs font-medium space-y-1 shadow-2xs">
                @foreach($errors->all() as $err)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $err }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Main View Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-5 relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[#E9EDEF] dark:border-[#222D34] bg-white/80 dark:bg-[#111B21]/80 backdrop-blur-md py-6 text-center text-xs text-[#667781] dark:text-[#8696A0] relative z-10 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-[#111B21] dark:text-[#E9EDEF]">SecureLink</span>
                <span>&bull;</span>
                <span>Tugas Besar Komunikasi Data &amp; Jaringan Komputer (KDJK) Kelompok 3</span>
            </div>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="{{ route('demo') }}" class="hover:text-[#008069] dark:hover:text-[#25D366] transition">Demonstrasi P2P</a>
                <a href="{{ route('settings') }}" class="hover:text-[#008069] dark:hover:text-[#25D366] transition">Keamanan &amp; Diagnostik</a>
                <a href="{{ route('tailscale.guide') }}" class="hover:text-[#008069] dark:hover:text-[#25D366] transition">Panduan Tailscale</a>
            </div>
        </div>
    </footer>

    <!-- Global Theme Script -->
    <script>
        function updateThemeIcons(mode) {
            const lightIcon = document.getElementById('theme-icon-light');
            const darkIcon = document.getElementById('theme-icon-dark');
            const sysIcon = document.getElementById('theme-icon-system');

            if (!lightIcon || !darkIcon || !sysIcon) return;

            lightIcon.classList.add('hidden');
            darkIcon.classList.add('hidden');
            sysIcon.classList.add('hidden');

            if (mode === 'light') {
                lightIcon.classList.remove('hidden');
            } else if (mode === 'dark') {
                darkIcon.classList.remove('hidden');
            } else {
                sysIcon.classList.remove('hidden');
            }
        }

        function setThemeMode(mode) {
            localStorage.setItem('securelink_theme', mode);
            if (mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            updateThemeIcons(mode);
        }

        function cycleTheme() {
            const current = localStorage.getItem('securelink_theme') || 'system';
            const modes = ['system', 'light', 'dark'];
            const next = modes[(modes.indexOf(current) + 1) % modes.length];
            setThemeMode(next);
        }

        function toggleMobileNav() {
            const el = document.getElementById('mobile-nav');
            if (el) el.classList.toggle('hidden');
        }

        // Initialize icons on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            const current = localStorage.getItem('securelink_theme') || 'system';
            updateThemeIcons(current);
        });
    </script>

    @stack('scripts')
</body>
</html>
