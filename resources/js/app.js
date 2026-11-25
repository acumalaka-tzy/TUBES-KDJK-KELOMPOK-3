//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
import { SecureLinkPeer } from './webrtc-chat';

window.SecureLinkPeer = SecureLinkPeer;

// Global keyboard listeners (e.g. Escape to dismiss modals and cancel active reply)
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const inviteModal = document.getElementById('invite-modal');
        if (inviteModal && !inviteModal.classList.contains('hidden')) {
            inviteModal.classList.add('hidden');
        }
        if (typeof window.cancelReply === 'function') {
            window.cancelReply();
        }
    }
});

console.log('[SecureLink] WebRTC & Chat modules loaded.');
