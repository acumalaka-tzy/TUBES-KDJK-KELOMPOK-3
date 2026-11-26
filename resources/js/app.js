import './echo';
import { SecureLinkPeer } from './webrtc-chat';
import * as SecureLinkE2EE from './e2ee-crypto';
import * as SecureLinkDtls from './dtls-verifier';

window.SecureLinkPeer = SecureLinkPeer;
window.SecureLinkE2EE = SecureLinkE2EE;
window.SecureLinkDtls = SecureLinkDtls;

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
