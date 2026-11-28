<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecureLink — Private P2P Messaging')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/securelink-logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/securelink-logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/securelink-logo.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Immediate Theme, Custom Palette & Font Size Initialization (Prevents FOUC) -->
    <script>
        (function() {
            const THEME_PRESETS = {
                'mint-teal': { primary: '#497173', secondary: '#71999A', accent: '#EC643E', bubble: '#DCEBEC' },
                'dark-teal': { primary: '#2F5D62', secondary: '#5E8B7E', accent: '#E07A5F', bubble: '#D3E4E5' },
                'warm-sand': { primary: '#6B705C', secondary: '#A5A58D', accent: '#CB997E', bubble: '#EDE8DF' },
            };
            window.SL_THEME_PRESETS = THEME_PRESETS;

            window.slGetReadableText = function(hex) {
                if (!hex || !/^#[0-9A-Fa-f]{6}$/.test(hex)) return '#20292B';
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance > 0.56 ? '#20292B' : '#F4F8F8';
            };

            window.applyChatFontSize = function(size) {
                const normalized = (size === 'normal' || !size) ? 'medium' : size;
                const map = {
                    small: { msg: '12px', reply: '11px' },
                    medium: { msg: '13.5px', reply: '12px' },
                    large: { msg: '15.5px', reply: '13.5px' },
                };
                const cfg = map[normalized] || map.medium;
                document.documentElement.style.setProperty('--sl-chat-font-size', cfg.msg);
                document.documentElement.style.setProperty('--sl-chat-reply-font-size', cfg.reply);
                localStorage.setItem('securelink_font_size', normalized);
            };

            window.applyCustomTheme = function(prefs) {
                if (!prefs) return;
                const root = document.documentElement.style;
                if (prefs.accentColor) root.setProperty('--sl-accent-coral', prefs.accentColor);
                if (prefs.bubbleColor) {
                    root.setProperty('--sl-bubble-out', prefs.bubbleColor);
                    root.setProperty('--sl-bubble-out-text', window.slGetReadableText(prefs.bubbleColor));
                }
                if (prefs.primaryColor) root.setProperty('--sl-primary-teal', prefs.primaryColor);
                if (prefs.secondaryColor) root.setProperty('--sl-secondary-teal', prefs.secondaryColor);
            };

            window.resetCustomTheme = function() {
                const root = document.documentElement.style;
                ['--sl-accent-coral', '--sl-bubble-out', '--sl-bubble-out-text', '--sl-primary-teal', '--sl-secondary-teal'].forEach(v => root.removeProperty(v));
                localStorage.removeItem('securelink_theme_prefs');
            };

            const savedTheme = localStorage.getItem('securelink_theme') || 'system';
            function applyTheme(theme) {
                const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            }
            applyTheme(savedTheme);

            try {
                const savedPrefs = JSON.parse(localStorage.getItem('securelink_theme_prefs') || 'null');
                if (savedPrefs) window.applyCustomTheme(savedPrefs);
            } catch (e) {}

            window.applyChatFontSize(localStorage.getItem('securelink_font_size') || 'medium');

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
        @include('partials.ui-feedback')
    @else
        <!-- Fixed Minimal Header -->
        <header class="fixed top-0 left-0 right-0 z-50 w-full bg-[var(--sl-white-surface)] border-b border-[var(--sl-divider)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ asset('images/securelink-logo.jpg') }}" alt="SecureLink Logo" class="w-8 h-8 rounded-lg object-cover bg-white shrink-0">
                    <span class="font-bold text-base tracking-tight text-[var(--sl-text-primary)] truncate">SecureLink</span>
                </a>

                @if(request()->routeIs('landing'))
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="#features" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition">Features</a>
                        <a href="#how-it-works" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition">How It Works</a>
                        <a href="#security" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition">Security</a>
                        <a href="#faq" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition">FAQ</a>
                    </nav>
                @endif

                <div class="flex items-center gap-2">
                    <button type="button" id="top-theme-btn" onclick="cycleTheme(this)" class="p-2 rounded-lg text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)] transition-colors" title="Ganti Tema">
                        <span class="sl-theme-icon-wrap inline-flex items-center justify-center transition-transform duration-300 ease-out">
                            <svg id="top-theme-sun" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                            </svg>
                            <svg id="top-theme-moon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </span>
                    </button>

                    <a href="{{ route('dashboard') }}" class="px-4 py-1.5 text-xs font-semibold rounded-lg sl-btn-coral">
                        Open App
                    </a>

                    @if(request()->routeIs('landing'))
                        <button type="button" onclick="toggleMobileNav()" class="md:hidden p-2 rounded-lg text-[var(--sl-text-secondary)] hover:bg-[var(--sl-main-surface)]" aria-label="Menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    @endif
                </div>
            </div>

            @if(request()->routeIs('landing'))
                <div id="mobile-nav" class="hidden md:hidden border-t border-[var(--sl-divider)] bg-[var(--sl-white-surface)] px-4 py-2.5 space-y-1">
                    <a href="#features" onclick="toggleMobileNav()" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Features</a>
                    <a href="#how-it-works" onclick="toggleMobileNav()" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">How It Works</a>
                    <a href="#security" onclick="toggleMobileNav()" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">Security</a>
                    <a href="#faq" onclick="toggleMobileNav()" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-text-primary)] hover:bg-[var(--sl-main-surface)]">FAQ</a>
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-[var(--sl-accent-coral)] hover:bg-[var(--sl-main-surface)]">Open App</a>
                </div>
            @endif
        </header>

        <!-- Main Content (Offset for fixed 56px header) -->
        <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 w-full pt-20 pb-8">
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
                    <a href="{{ route('landing') }}#features" class="hover:text-[var(--sl-primary-teal)] transition">Features</a>
                    <a href="{{ route('landing') }}#how-it-works" class="hover:text-[var(--sl-primary-teal)] transition">How It Works</a>
                    <a href="{{ route('landing') }}#security" class="hover:text-[var(--sl-primary-teal)] transition">Security</a>
                    <a href="{{ route('landing') }}#faq" class="hover:text-[var(--sl-primary-teal)] transition">FAQ</a>
                </div>
            </div>
        </footer>

        @include('partials.ui-feedback')
    @endif

    <script>
        function updateThemeIcons(mode) {
            const isDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            const topSun = document.getElementById('top-theme-sun');
            const topMoon = document.getElementById('top-theme-moon');
            if (topSun && topMoon) {
                topSun.classList.toggle('hidden', isDark);
                topMoon.classList.toggle('hidden', !isDark);
            }

            const navSun = document.getElementById('nav-theme-sun');
            const navMoon = document.getElementById('nav-theme-moon');
            if (navSun && navMoon) {
                navSun.classList.toggle('hidden', isDark);
                navMoon.classList.toggle('hidden', !isDark);
            }
        }

        function setThemeMode(mode) {
            localStorage.setItem('securelink_theme', mode);
            const isDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
            updateThemeIcons(mode);
        }

        function cycleTheme(btnEl) {
            const currentlyDark = document.documentElement.classList.contains('dark');
            setThemeMode(currentlyDark ? 'light' : 'dark');
            const targetBtn = btnEl || document.getElementById('nav-theme-btn') || document.getElementById('top-theme-btn');
            const iconWrap = targetBtn ? targetBtn.querySelector('.sl-theme-icon-wrap') : null;
            if (iconWrap) {
                iconWrap.classList.add('rotate-180', 'scale-110');
                setTimeout(() => iconWrap.classList.remove('rotate-180', 'scale-110'), 300);
            }
        }

        function toggleMobileNav() {
            document.getElementById('mobile-nav')?.classList.toggle('hidden');
        }

        function syncMobileViewportHeight() {
            const root = document.documentElement;
            if (window.innerWidth >= 768) {
                root.style.removeProperty('--sl-vvh');
                return;
            }
            const vv = window.visualViewport;
            const height = vv ? Math.round(vv.height) : window.innerHeight;
            root.style.setProperty('--sl-vvh', `${height}px`);
            if (vv && vv.offsetTop > 0) {
                window.scrollTo(0, 0);
            }
            window.dispatchEvent(new CustomEvent('securelink:keyboard-resize', { detail: { height } }));
        }

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', syncMobileViewportHeight);
            window.visualViewport.addEventListener('scroll', syncMobileViewportHeight);
        }
        window.addEventListener('resize', syncMobileViewportHeight);

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcons(localStorage.getItem('securelink_theme') || 'system');
            syncMobileViewportHeight();
        });
    </script>

    @stack('scripts')
</body>
</html>
