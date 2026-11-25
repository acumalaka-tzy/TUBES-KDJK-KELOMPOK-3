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
