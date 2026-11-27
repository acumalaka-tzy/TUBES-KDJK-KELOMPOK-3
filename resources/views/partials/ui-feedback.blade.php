<!-- Global Toast Container (Top-Right, Auto-dismiss 4s) -->
<div id="sl-toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 max-w-sm w-[calc(100vw-2rem)] pointer-events-none"></div>

<!-- Global Confirmation Modal (Replaces native window.confirm) -->
<div id="sl-confirm-modal" class="fixed inset-0 z-[90] hidden bg-black/50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="sl-confirm-title">
    <div class="sl-surface rounded-xl max-w-sm w-full p-5 space-y-4 shadow-xl">
        <div class="space-y-1.5">
            <h3 id="sl-confirm-title" class="text-sm font-bold text-[var(--sl-text-primary)]">Konfirmasi</h3>
            <p id="sl-confirm-message" class="text-xs text-[var(--sl-text-secondary)] leading-relaxed"></p>
        </div>
        <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" id="sl-confirm-cancel" class="px-4 py-2 rounded-lg sl-btn-secondary text-xs font-semibold">
                Cancel
            </button>
            <button type="button" id="sl-confirm-accept" class="px-4 py-2 rounded-lg sl-btn-coral text-xs font-semibold">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        let confirmCallback = null;

        window.slToast = function(message, type = 'info', duration = 4000, action = null) {
            const container = document.getElementById('sl-toast-container');
            if (!container || !message) return;

            const toast = document.createElement('div');
            const borderClass = type === 'error'
                ? 'border-[var(--sl-accent-coral)]'
                : (type === 'success' ? 'border-[var(--sl-primary-teal)]' : 'border-[var(--sl-divider)]');
            const dotClass = type === 'error'
                ? 'bg-[var(--sl-accent-coral)]'
                : (type === 'success' ? 'bg-[var(--sl-success)]' : 'bg-[var(--sl-primary-teal)]');

            toast.className = `pointer-events-auto sl-surface border-l-4 ${borderClass} rounded-lg px-3.5 py-2.5 shadow-lg flex items-start justify-between gap-2.5 text-xs transition-all duration-200`;
            toast.innerHTML = `
                <div class="flex items-start gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full ${dotClass} mt-1 shrink-0"></span>
                    <div class="space-y-1 min-w-0">
                        <div class="text-[var(--sl-text-primary)] font-medium break-words"></div>
                        ${action ? `<button type="button" class="toast-action text-[11px] font-bold text-[var(--sl-primary-teal)] hover:underline"></button>` : ''}
                    </div>
                </div>
                <button type="button" aria-label="Tutup notifikasi" class="toast-close text-[var(--sl-text-secondary)] hover:text-[var(--sl-text-primary)] font-bold text-sm leading-none shrink-0">&times;</button>
            `;
            toast.querySelector('.font-medium').textContent = message;

            if (action && action.label && typeof action.onClick === 'function') {
                const actBtn = toast.querySelector('.toast-action');
                actBtn.textContent = action.label;
                actBtn.onclick = () => { action.onClick(); removeToast(); };
            }

            let timer = null;
            const removeToast = () => {
                if (timer) clearTimeout(timer);
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(12px)';
                setTimeout(() => toast.remove(), 200);
            };

            toast.querySelector('.toast-close').onclick = removeToast;
            container.appendChild(toast);

            if (duration > 0) {
                timer = setTimeout(removeToast, duration);
            }
        };

        window.slConfirm = function({ title = 'Konfirmasi', message = '', confirmLabel = 'Confirm', cancelLabel = 'Cancel', onConfirm = null }) {
            const modal = document.getElementById('sl-confirm-modal');
            if (!modal) {
                if (typeof onConfirm === 'function') onConfirm();
                return;
            }
            document.getElementById('sl-confirm-title').textContent = title;
            document.getElementById('sl-confirm-message').textContent = message;
            document.getElementById('sl-confirm-accept').textContent = confirmLabel;
            document.getElementById('sl-confirm-cancel').textContent = cancelLabel;
            confirmCallback = onConfirm;
            modal.classList.remove('hidden');
        };

        window.slConfirmSignOut = function(formEl) {
            window.slConfirm({
                title: 'Sign Out',
                message: 'Apakah Anda yakin ingin keluar dari akun SecureLink?',
                confirmLabel: 'Sign Out',
                cancelLabel: 'Cancel',
                onConfirm: () => {
                    if (formEl) formEl.submit();
                },
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('sl-confirm-modal');
            const cancelBtn = document.getElementById('sl-confirm-cancel');
            const acceptBtn = document.getElementById('sl-confirm-accept');

            if (cancelBtn) {
                cancelBtn.onclick = () => {
                    confirmCallback = null;
                    modal?.classList.add('hidden');
                };
            }
            if (acceptBtn) {
                acceptBtn.onclick = () => {
                    const cb = confirmCallback;
                    confirmCallback = null;
                    modal?.classList.add('hidden');
                    if (typeof cb === 'function') cb();
                };
            }

            // Convert Laravel session flash messages into auto-dismiss 4s toasts
            @if(session('success'))
                window.slToast(@json(session('success')), 'success', 4000);
            @endif
            @if(session('info'))
                window.slToast(@json(session('info')), 'info', 4000);
            @endif
            @if(session('warning'))
                window.slToast(@json(session('warning')), 'error', 4500);
            @endif
            @if($errors->any())
                window.slToast(@json($errors->first()), 'error', 5000);
            @endif

            // Session Expiration Monitor (Aligned with Laravel config('session.lifetime'))
            @if(session()->has('securelink_user_id'))
                const lifetimeMs = {{ max(2, (int) config('session.lifetime', 120)) }} * 60 * 1000;
                const warnAfterMs = Math.max(60000, lifetimeMs - (5 * 60 * 1000));
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                setTimeout(() => {
                    window.slToast('Sesi Anda akan segera berakhir dalam 5 menit.', 'info', 8000, {
                        label: 'Perpanjang Sesi',
                        onClick: () => {
                            fetch('/api/identity/heartbeat', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            }).then(r => {
                                if (r.ok) window.slToast('Sesi berhasil diperpanjang.', 'success', 3500);
                            }).catch(() => {});
                        }
                    });
                }, warnAfterMs);
            @endif
        });
    })();
</script>
