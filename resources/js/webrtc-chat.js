/**
 * SecureLink WebRTC & E2EE Client Engine
 * Implements PRD Section 3.4, 4.1, 6.1-6.3, and 12.1.
 *
 * Features:
 * - RTCPeerConnection with STUN servers
 * - RTCDataChannel for zero-server-retention direct chat
 * - Web Crypto API (ECDH P-256 + HKDF-SHA256 + AES-GCM 256-bit + 96-bit Nonce/IV)
 * - Reliable delivery with ACK and exponential retry
 * - Ephemeral message automatic destruction
 * - Honest connection detection (Direct P2P vs TURN Relay)
 */

import {
    E2EEKeyManager,
    NonceManager,
    bytesToBase64,
    base64ToBytes,
    computePublicKeyId,
    sanitizePublicJwk,
    derivePeerEncryptionKey,
    encryptAesGcm,
    decryptAesGcm,
} from './e2ee-crypto.js';
import {
    inspectSdpDtlsFingerprint,
    verifyPeerDtlsTransport,
    evaluateTransportAndE2eeSecurity,
} from './dtls-verifier.js';

export class SecureLinkPeer {
    constructor(config) {
        this.roomId = config.roomId;
        this.userId = config.userId;
        this.userName = config.userName;
        this.userAvatar = config.userAvatar;
        this.isCreator = config.isCreator || false;
        this.e2eeEnabled = config.e2eeEnabled !== false;
        this.onMessageReceived = config.onMessageReceived || (() => {});
        this.onStatusChange = config.onStatusChange || (() => {});
        this.onSecurityStatusChange = config.onSecurityStatusChange || (() => {});
        this.onSecurityEvent = config.onSecurityEvent || (() => {});
        this.onPeerListChange = config.onPeerListChange || (() => {});
        this.onMessageAck = config.onMessageAck || (() => {});

        this.peers = new Map(); // peerId -> { pc, dc, sharedKey, nonceManager, remoteKeyId, e2eeStatus, connectionType }
        this.messageStore = new Map(); // msgId -> { payload, retries, timeoutId }
        this.seenMessages = new Set();
        this.keyManager = new E2EEKeyManager();
        this.localKeyPair = null;
        this.e2eeStatus = this.e2eeEnabled ? 'initializing' : 'disabled';

        this.iceServers = [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' }
        ];

        this.cryptoReady = this.initCrypto();
    }

    async initCrypto() {
        if (!this.e2eeEnabled) {
            this.e2eeStatus = 'disabled';
            return null;
        }

        if (!globalThis.crypto || !globalThis.crypto.subtle) {
            this.e2eeStatus = 'failed';
            this.onSecurityEvent('e2ee_failed', { reason: 'web_crypto_unavailable' });
            console.warn('[SecureLink] Web Crypto API not available in this environment');
            return null;
        }

        try {
            const state = await this.keyManager.initialize();
            this.localKeyPair = state.keyPair;
            this.e2eeStatus = 'key_pair_ready';
            this.onSecurityStatusChange(this.getE2eeSummary());
            console.log('[SecureLink] ECDH P-256 non-extractable Key Pair initialized');
            return state;
        } catch (e) {
            this.e2eeStatus = 'failed';
            this.onSecurityEvent('e2ee_failed', { reason: 'key_pair_generation_failed' });
            console.error('[SecureLink] Failed to generate ECDH keys:', e);
            return null;
        }
    }

    async exportPublicKey() {
        await this.cryptoReady;
        const bundle = await this.keyManager.getPublicKeyBundle();
        this.localKeyPair = this.keyManager.keyPair;
        return bundle.publicJwk;
    }

    async deriveSharedKey(remoteJwkKey, targetUserId = 'peer', peerRecord = null) {
        try {
            await this.cryptoReady;
            if (!this.keyManager.keyPair) {
                await this.keyManager.initialize();
                this.localKeyPair = this.keyManager.keyPair;
            }

            const localBundle = await this.keyManager.getPublicKeyBundle();
            const remoteKeyId = await computePublicKeyId(sanitizePublicJwk(remoteJwkKey));
            const saltSeed = [localBundle.keyId, remoteKeyId].sort().join(':');

            const derived = await derivePeerEncryptionKey(this.keyManager.keyPair, remoteJwkKey, {
                roomId: this.roomId || 'default-room',
                localUserId: this.userId,
                remoteUserId: targetUserId,
                extraSalt: saltSeed,
            });

            if (peerRecord) {
                peerRecord.sharedKey = derived.aesKey;
                peerRecord.remoteKeyId = derived.remoteKeyId;
                peerRecord.e2eeStatus = 'active';
                if (!peerRecord.nonceManager) {
                    peerRecord.nonceManager = new NonceManager();
                } else {
                    peerRecord.nonceManager.reset();
                }
            }

            this.e2eeStatus = 'active';
            this.onSecurityEvent('e2ee_initialized', {
                target_user_id: targetUserId,
                algorithm: derived.algorithm,
            });
            this.onSecurityStatusChange(this.getE2eeSummary());

            return derived.aesKey;
        } catch (err) {
            if (peerRecord) {
                peerRecord.sharedKey = null;
                peerRecord.e2eeStatus = 'failed';
            }
            this.e2eeStatus = 'failed';
            this.onSecurityEvent('e2ee_failed', { target_user_id: targetUserId });
            this.onSecurityStatusChange(this.getE2eeSummary());
            console.error('[SecureLink] ECDH+HKDF key derivation error:', err);
            return null;
        }
    }

    async encryptText(text, peerOrKey, messageId = '') {
        const isPeerRecord = peerOrKey && typeof peerOrKey === 'object' && 'targetUserId' in peerOrKey;
        const sharedKey = isPeerRecord ? peerOrKey.sharedKey : peerOrKey;
        const nonceManager = isPeerRecord ? peerOrKey.nonceManager : new NonceManager();

        if (!this.e2eeEnabled) {
            return {
                ciphertext: bytesToBase64(new TextEncoder().encode(text)),
                iv: null,
                encrypted: false,
            };
        }

        if (!sharedKey) {
            throw new Error('Kunci E2EE belum tersedia untuk peer; pengiriman plaintext dibatalkan.');
        }

        return await encryptAesGcm(text, sharedKey, nonceManager, {
            roomId: this.roomId,
            senderId: this.userId,
            messageId,
        });
    }

    async decryptPayload(ciphertextB64, ivB64, peerOrKey, metadata = {}) {
        const isPeerRecord = peerOrKey && typeof peerOrKey === 'object' && 'targetUserId' in peerOrKey;
        const sharedKey = isPeerRecord ? peerOrKey.sharedKey : peerOrKey;
        const nonceManager = isPeerRecord ? peerOrKey.nonceManager : null;

        if (!this.e2eeEnabled && !ivB64 && metadata.encrypted === false) {
            return new TextDecoder().decode(base64ToBytes(ciphertextB64));
        }

        if (this.e2eeEnabled && (!ivB64 || metadata.encrypted === false || !sharedKey)) {
            this.onSecurityEvent('e2ee_failed', { reason: 'downgrade_or_missing_key' });
            return '⚠️ [Pesan ditolak: payload tidak terenkripsi atau kunci E2EE belum aktif]';
        }

        try {
            return await decryptAesGcm(
                {
                    ciphertext: ciphertextB64,
                    iv: ivB64,
                    encrypted: metadata.encrypted !== false,
                },
                sharedKey,
                nonceManager,
                metadata.aadContext || null
            );
        } catch (err) {
            this.onSecurityEvent('e2ee_failed', { reason: 'decryption_or_auth_tag_failed' });
            console.error('[SecureLink] Decryption failed:', err);
            return '⚠️ [Pesan gagal didekripsi: integritas rusak]';
        }
    }

    async rotateSessionKeys() {
        if (!this.e2eeEnabled) return null;

        const state = await this.keyManager.rotateKeyPair();
        this.localKeyPair = state.keyPair;
        this.e2eeStatus = 'key_pair_ready';

        for (const peerRecord of this.peers.values()) {
            peerRecord.sharedKey = null;
            peerRecord.remoteKeyId = null;
            peerRecord.e2eeStatus = 'exchanging';
            if (peerRecord.nonceManager) {
                peerRecord.nonceManager.reset();
            }
            if (peerRecord.dc && peerRecord.dc.readyState === 'open') {
                peerRecord.dc.send(JSON.stringify({
                    type: 'key-exchange',
                    senderId: this.userId,
                    keyId: state.keyId,
                    publicKey: state.publicJwk,
                }));
            }
        }

        this.onSecurityStatusChange(this.getE2eeSummary());
        return state;
    }

    getE2eeSummary() {
        const peerStatuses = Array.from(this.peers.values()).map((p) => p.e2eeStatus);
        const activeCount = peerStatuses.filter((s) => s === 'active').length;
        return {
            enabled: this.e2eeEnabled,
            status: this.e2eeStatus,
            algorithm: 'ECDH P-256 + HKDF-SHA256 + AES-GCM-256',
            activePeerKeys: activeCount,
            totalPeers: this.peers.size,
            keyManager: this.keyManager.getStatus(),
        };
    }

    createPeerConnection(targetUserId) {
        if (this.peers.has(targetUserId)) {
            return this.peers.get(targetUserId);
        }

        const pc = new RTCPeerConnection({ iceServers: this.iceServers });
        const peerRecord = {
            targetUserId,
            pc,
            dc: null,
            sharedKey: null,
            remoteKeyId: null,
            nonceManager: new NonceManager(),
            e2eeStatus: this.e2eeEnabled ? 'exchanging' : 'disabled',
            dtlsState: 'new',
            dtlsVerified: false,
            dtlsCipher: null,
            srtpCipher: null,
            tlsVersion: null,
            sdpDtls: null,
            connectionType: 'unknown'
        };

        this.onSecurityEvent('peer_connection_started', { target_user_id: targetUserId });

        pc.onicecandidate = (event) => {
            if (event.candidate && this.signalSender) {
                this.signalSender(targetUserId, {
                    type: 'candidate',
                    candidate: event.candidate
                });
            }
        };

        pc.oniceconnectionstatechange = async () => {
            console.log(`[SecureLink] ICE State with ${targetUserId}: ${pc.iceConnectionState}`);
            await this.verifyDtlsStatus(peerRecord);
            this.onStatusChange(this.getOverallStatus());
            if (pc.iceConnectionState === 'failed') {
                this.onSecurityEvent('webrtc_failed', { target_user_id: targetUserId, ice_state: pc.iceConnectionState });
            }
        };

        pc.onconnectionstatechange = async () => {
            await this.verifyDtlsStatus(peerRecord);
            this.onStatusChange(this.getOverallStatus());
            if (pc.connectionState === 'connected') {
                this.onSecurityEvent('webrtc_connected', {
                    target_user_id: targetUserId,
                    connection_type: peerRecord.connectionType,
                });
            } else if (pc.connectionState === 'failed') {
                this.onSecurityEvent('webrtc_failed', { target_user_id: targetUserId });
            }
        };

        pc.ondatachannel = (event) => {
            peerRecord.dc = event.channel;
            this.setupDataChannel(peerRecord);
        };

        this.peers.set(targetUserId, peerRecord);
        return peerRecord;
    }

    attachDtlsTransportListener(peerRecord) {
        const dtlsTransport = peerRecord.pc?.sctp?.transport;
        if (!dtlsTransport || peerRecord._dtlsBound) return;
        peerRecord._dtlsBound = true;
        dtlsTransport.onstatechange = () => {
            this.verifyDtlsStatus(peerRecord);
        };
    }

    async verifyDtlsStatus(peerRecord) {
        if (!peerRecord?.pc) return null;
        this.attachDtlsTransportListener(peerRecord);

        const prevState = peerRecord.dtlsState;
        const result = await verifyPeerDtlsTransport(peerRecord.pc);

        peerRecord.dtlsState = result.dtlsState;
        peerRecord.dtlsVerified = result.dtlsVerified;
        peerRecord.dtlsCipher = result.dtlsCipher;
        peerRecord.srtpCipher = result.srtpCipher;
        peerRecord.tlsVersion = result.tlsVersion;
        if (result.connectionType !== 'unknown') {
            peerRecord.connectionType = result.connectionType;
        }

        if (prevState !== result.dtlsState) {
            if (result.dtlsState === 'connected') {
                this.onSecurityEvent('dtls_connected', {
                    target_user_id: peerRecord.targetUserId,
                    dtls_cipher: result.dtlsCipher,
                    tls_version: result.tlsVersion,
                });
            } else if (result.dtlsState === 'failed') {
                this.onSecurityEvent('dtls_failed', { target_user_id: peerRecord.targetUserId });
            }
            this.onSecurityStatusChange(this.getDtlsSummary());
        }

        return result;
    }

    getDtlsSummary() {
        const peers = Array.from(this.peers.values());
        const connectedDtls = peers.filter((p) => p.dtlsVerified).length;
        const anyFailed = peers.some((p) => p.dtlsState === 'failed');
        const anyConnecting = peers.some((p) => p.dtlsState === 'connecting');

        let overallDtlsState = 'unverified';
        if (connectedDtls > 0) {
            overallDtlsState = 'connected';
        } else if (anyFailed) {
            overallDtlsState = 'failed';
        } else if (anyConnecting) {
            overallDtlsState = 'connecting';
        }

        const firstConnected = peers.find((p) => p.dtlsVerified);
        const evaluation = evaluateTransportAndE2eeSecurity({
            dtlsState: overallDtlsState,
            e2eeStatus: this.e2eeStatus,
            e2eeEnabled: this.e2eeEnabled,
            dataChannelState: peers.some((p) => p.dc?.readyState === 'open') ? 'open' : 'closed',
        });

        return {
            dtlsState: overallDtlsState,
            dtlsVerified: overallDtlsState === 'connected',
            dtlsCipher: firstConnected?.dtlsCipher || null,
            srtpCipher: firstConnected?.srtpCipher || null,
            tlsVersion: firstConnected?.tlsVersion || null,
            connectedDtlsPeers: connectedDtls,
            totalPeers: peers.length,
            e2eeStatus: this.e2eeStatus,
            evaluation,
        };
    }

    setupDataChannel(peerRecord) {
        const dc = peerRecord.dc;
        dc.onopen = async () => {
            console.log(`[SecureLink] DataChannel opened with ${peerRecord.targetUserId}`);
            await this.verifyDtlsStatus(peerRecord);
            this.onStatusChange(this.getOverallStatus());

            if (this.e2eeEnabled) {
                await this.cryptoReady;
                const bundle = await this.keyManager.getPublicKeyBundle();
                peerRecord.e2eeStatus = 'exchanging';
                dc.send(JSON.stringify({
                    type: 'key-exchange',
                    senderId: this.userId,
                    keyId: bundle.keyId,
                    publicKey: bundle.publicJwk
                }));
            }
        };

        dc.onmessage = async (event) => {
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'key-exchange') {
                    if (!this.e2eeEnabled) return;
                    const derived = await this.deriveSharedKey(data.publicKey, peerRecord.targetUserId, peerRecord);
                    if (derived && dc.readyState === 'open') {
                        dc.send(JSON.stringify({
                            type: 'key-exchange-ack',
                            senderId: this.userId,
                            keyId: this.keyManager.keyId,
                        }));
                    }
                    console.log(`[SecureLink] E2EE HKDF+AES-GCM key derived for peer ${peerRecord.targetUserId}`);
                    return;
                }

                if (data.type === 'key-exchange-ack') {
                    peerRecord.peerAckedKey = true;
                    this.onSecurityStatusChange(this.getE2eeSummary());
                    return;
                }

                if (data.type === 'ack') {
                    this.handleAck(data.messageId);
                    this.onMessageAck(data.messageId);
                    this.onSecurityEvent('message_delivery_acknowledged', { message_id: data.messageId });
                    return;
                }

                if (data.type === 'chat') {
                    if (this.seenMessages.has(data.id)) {
                        // Send duplicate ACK and ignore payload
                        dc.send(JSON.stringify({ type: 'ack', messageId: data.id }));
                        return;
                    }

                    this.seenMessages.add(data.id);
                    // Send ACK back to sender
                    dc.send(JSON.stringify({ type: 'ack', messageId: data.id }));

                    const aadContext = {
                        roomId: this.roomId,
                        senderId: data.senderId || peerRecord.targetUserId,
                        messageId: data.id,
                    };
                    const text = await this.decryptPayload(data.ciphertext, data.iv, peerRecord, {
                        encrypted: data.encrypted,
                        aadContext,
                    });
                    const verifiedE2ee = Boolean(data.iv && data.encrypted !== false && peerRecord.sharedKey);
                    this.onMessageReceived({
                        id: data.id,
                        senderId: data.senderId,
                        senderName: data.senderName,
                        text,
                        timestamp: data.timestamp,
                        ephemeralDuration: data.ephemeralDuration,
                        replyTo: data.replyTo,
                        isE2EE: verifiedE2ee
                    });
                }
            } catch (e) {
                console.error('[SecureLink] DataChannel message error:', e);
            }
        };

        dc.onclose = () => {
            console.log(`[SecureLink] DataChannel closed with ${peerRecord.targetUserId}`);
            this.onStatusChange(this.getOverallStatus());
        };
    }

    async initiateOffer(targetUserId) {
        const peer = this.createPeerConnection(targetUserId);
        peer.dc = peer.pc.createDataChannel('securelink-datachannel', { ordered: true });
        this.setupDataChannel(peer);

        const offer = await peer.pc.createOffer();
        await peer.pc.setLocalDescription(offer);

        if (this.signalSender) {
            this.signalSender(targetUserId, {
                type: 'offer',
                sdp: offer
            });
        }
    }

    async handleSignal(fromUserId, signal) {
        if (fromUserId === this.userId) return;
        const peer = this.createPeerConnection(fromUserId);

        if (signal.type === 'offer') {
            peer.sdpDtls = inspectSdpDtlsFingerprint(signal.sdp?.sdp);
            await peer.pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
            const answer = await peer.pc.createAnswer();
            await peer.pc.setLocalDescription(answer);
            this.onSecurityEvent('sdp_offer_exchanged', { target_user_id: fromUserId });

            if (this.signalSender) {
                this.signalSender(fromUserId, {
                    type: 'answer',
                    sdp: answer
                });
            }
        } else if (signal.type === 'answer') {
            peer.sdpDtls = inspectSdpDtlsFingerprint(signal.sdp?.sdp);
            await peer.pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
            this.onSecurityEvent('sdp_answer_exchanged', { target_user_id: fromUserId });
        } else if (signal.type === 'candidate' && signal.candidate) {
            try {
                await peer.pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
                this.onSecurityEvent('ice_candidate_exchanged', { target_user_id: fromUserId });
            } catch (err) {
                console.error('[SecureLink] Error adding ICE candidate:', err);
            }
        }
    }

    async checkConnectionType(pc, peerRecord = null) {
        try {
            const result = await verifyPeerDtlsTransport(pc);
            if (result.connectionType !== 'unknown') {
                this.connectionType = result.connectionType;
                if (peerRecord) {
                    peerRecord.connectionType = result.connectionType;
                }
            }
            return result;
        } catch (e) {
            return null;
        }
    }

    getOverallStatus() {
        let connectedCount = 0;
        let isRelay = false;

        this.peers.forEach(peer => {
            if (peer.dc && peer.dc.readyState === 'open') {
                connectedCount++;
                if (peer.connectionType === 'relay') isRelay = true;
            }
        });

        if (connectedCount > 0) {
            return {
                status: 'connected',
                label: isRelay ? 'TURN Relay' : 'P2P Direct',
                color: isRelay ? 'text-amber-500' : 'text-emerald-500',
                count: connectedCount
            };
        }

        return {
            status: 'waiting',
            label: 'Menunggu Peer...',
            color: 'text-blue-500',
            count: 0
        };
    }

    async broadcastMessage(text, ephemeralDuration = 0, replyTo = null) {
        const messageId = 'msg_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const promises = Array.from(this.peers.values()).map(async (peer) => {
            if (!peer.dc || peer.dc.readyState !== 'open') return false;

            try {
                const { ciphertext, iv, encrypted, algorithm } = await this.encryptText(text, peer, messageId);
                const payload = JSON.stringify({
                    type: 'chat',
                    id: messageId,
                    senderId: this.userId,
                    senderName: this.userName,
                    ciphertext,
                    iv,
                    encrypted,
                    algorithm: algorithm || null,
                    timestamp,
                    ephemeralDuration,
                    replyTo
                });

                peer.dc.send(payload);
                return true;
            } catch (err) {
                console.error(`[SecureLink] Gagal mengirim pesan ke ${peer.targetUserId}:`, err);
                this.onSecurityEvent('e2ee_failed', {
                    target_user_id: peer.targetUserId,
                    reason: 'encrypt_aborted',
                });
                return false;
            }
        });

        await Promise.all(promises);

        // Track for retry & ACK
        this.messageStore.set(messageId, {
            id: messageId,
            text,
            ephemeralDuration,
            replyTo,
            timestamp,
            delivered: false
        });

        return {
            id: messageId,
            timestamp,
            text,
            ephemeralDuration,
            replyTo
        };
    }

    handleAck(messageId) {
        const item = this.messageStore.get(messageId);
        if (item) {
            item.delivered = true;
        }
    }
}
