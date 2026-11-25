<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecureLink — Secure Peer-to-Peer Messaging')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0b0f19',
                            surface: '#111827',
                            card: '#151d30',
                            border: '#1f293d',
                            text: '#f3f4f6',
                            muted: '#9ca3af'
                        },
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth theme transitions */
        html.dark {
            color-scheme: dark;
        }
        body {
            transition: background-color 0.2s ease, color 0.2s ease;
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-900 dark:bg-[#0b0f19] dark:text-slate-100 flex flex-col font-sans antialiased selection:bg-blue-500 selection:text-white">

    <!-- Theme Initialization Script -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'system';
        function applyTheme(theme) {
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        applyTheme(savedTheme);
    </script>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 w-full border-b border-slate-200 dark:border-[#1f293d] bg-white/80 dark:bg-[#111827]/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 font-bold text-lg tracking-tight hover:opacity-90 transition">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-sm shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-slate-900 dark:text-white font-extrabold text-base leading-tight">SecureLink</span>
                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold tracking-wider uppercase">P2P Network Lab</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-1">
                @if(session()->has('securelink_user_id'))
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-100 dark:hover:bg-[#1f293d] {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-300' }}">Dashboard</a>
                    <a href="{{ route('rooms.create') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-100 dark:hover:bg-[#1f293d] {{ request()->routeIs('rooms.create') ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-300' }}">Buat Room</a>
                    <a href="{{ route('rooms.join') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-100 dark:hover:bg-[#1f293d] {{ request()->routeIs('rooms.join') ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-300' }}">Gabung</a>
                @endif
                <a href="{{ route('demo') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-100 dark:hover:bg-[#1f293d] {{ request()->routeIs('demo') ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-300' }}">Demonstrasi P2P</a>
                <a href="{{ route('settings') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-100 dark:hover:bg-[#1f293d] {{ request()->routeIs('settings') ? 'text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-300' }}">Settings</a>
            </nav>

            <!-- User Status & Theme Switcher -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button type="button" onclick="toggleTheme()" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#1f293d] transition" title="Ganti Tema">
                    <svg id="theme-icon-sun" class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg id="theme-icon-moon" class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                @if(isset($user) && $user)
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-[#1f293d]">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold uppercase shadow-sm" style="background-color: {{ $user->avatar_color }}">
                            {{ substr($user->display_name, 0, 1) }}
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-semibold text-slate-900 dark:text-white leading-none">{{ $user->display_name }}</span>
                            <span class="text-[10px] text-emerald-500 dark:text-emerald-400 font-mono mt-0.5">● Online</span>
                        </div>
                    </div>
                @else
                    <a href="{{ route('identity.setup') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                        Setup Identitas
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Flash Messages / Toast Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/30 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 rounded-xl border border-blue-200 dark:border-blue-800/50 bg-blue-50 dark:bg-blue-950/30 p-4 text-sm text-blue-800 dark:text-blue-300 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 p-4 text-sm text-rose-800 dark:text-rose-300 shadow-sm">
                <div class="font-semibold mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>Terjadi kesalahan:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 dark:text-rose-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-[#1f293d] bg-white dark:bg-[#111827] py-6 text-xs text-slate-500 dark:text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700 dark:text-slate-300">SecureLink v2.1</span>
                <span>&bull;</span>
                <span>Praktikum Komunikasi Data &amp; Jaringan Komputer</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400 dark:text-slate-500">
                <span>WebRTC DataChannel</span>
                <span>&bull;</span>
                <span>DTLS &amp; E2EE</span>
                <span>&bull;</span>
                <span>Tailscale VPN</span>
            </div>
        </div>
    </footer>

    <!-- Theme Switcher JavaScript -->
    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            const newTheme = isDark ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        }
    </script>
    @stack('scripts')
</body>
</html>
