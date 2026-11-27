/**
 * SecureLink WebRTC & E2EE Client Engine
 * Implements PRD Section 3.4, 3.7, 4.1, 6.1-6.3, and 12.1.
 *
 * Features:
 * - Dynamic STUN/TURN ICE server configuration
 * - RTCPeerConnection with polite/impolite glare-free SDP Offer/Answer exchange
 * - ICE Candidate buffering queue until remoteDescription is set
 * - RTCDataChannel with ACK, exponential retry, and ping/pong RTT measurement
 * - Web Crypto API (ECDH P-256 + HKDF-SHA256 + AES-GCM 256-bit + 96-bit Nonce/IV)
 * - Verified DTLS transport monitoring separate from application-layer E2EE
 * - Honest connection detection (Direct P2P vs TURN Relay via RTCIceCandidatePairStats)
 * - Automatic disconnect cleanup and ICE-restart reconnection
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
        this.userAvatarColor = config.userAvatarColor || '#008069';
        this.userAvatarIcon = config.userAvatarIcon || 'initial';
        this.isCreator = config.isCreator || false;
        this.e2eeEnabled = config.e2eeEnabled !== false;

        this.onMessageReceived = config.onMessageReceived || (() => {});
        this.onReactionReceived = config.onReactionReceived || (() => {});
        this.onMessageDeleted = config.onMessageDeleted || (() => {});
        this.onStatusChange = config.onStatusChange || (() => {});
        this.onSecurityStatusChange = config.onSecurityStatusChange || (() => {});
        this.onSecurityEvent = config.onSecurityEvent || (() => {});
        this.onPeerListChange = config.onPeerListChange || (() => {});
        this.onMessageAck = config.onMessageAck || (() => {});
        this.onDiagnosticsUpdate = config.onDiagnosticsUpdate || (() => {});
        this.signalSender = config.signalSender || null;

        this.peers = new Map();
        this.messageStore = new Map();
        this.seenMessages = new Set();
        this.keyManager = new E2EEKeyManager();
        this.localKeyPair = null;
        this.destroyed = false;
        this.e2eeStatus = this.e2eeEnabled ? 'initializing' : 'disabled';

        this.iceServers = config.iceServers || [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
        ];
        this.iceTransportPolicy = config.iceTransportPolicy || 'all';
        this.iceCandidatePoolSize = config.iceCandidatePoolSize ?? 2;
        this.hasTurn = Boolean(config.hasTurn);

        this.maxReconnectAttempts = 4;
        this.maxMessageRetries = 3;

        this.cryptoReady = this.initCrypto();
    }

    async loadIceConfig() {
        try {
            const response = await fetch('/api/webrtc/ice-servers', {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return false;
            const data = await response.json();
            if (data.success && Array.isArray(data.iceServers) && data.iceServers.length > 0) {
                this.iceServers = data.iceServers;
                this.iceTransportPolicy = data.iceTransportPolicy || 'all';
                this.iceCandidatePoolSize = data.iceCandidatePoolSize ?? 2;
                this.hasTurn = Boolean(data.hasTurn);
                return true;
            }
        } catch (err) {
            console.warn('[SecureLink] Failed to fetch ICE config, using default STUN:', err);
        }
        return false;
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
        if (!this.e2eeEnabled) return null;
        const bundle = await this.keyManager.getPublicKeyBundle();
        this.localKeyPair = this.keyManager.keyPair;
        return bundle.publicJwk;
    }

    async deriveSharedKey(remoteJwkKey, targetUserId = 'peer', peerRecord = null) {
        if (!remoteJwkKey) return null;
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
            return '[Pesan gagal didekripsi: integritas rusak]';
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

        const pc = new RTCPeerConnection({
            iceServers: this.iceServers,
            iceTransportPolicy: this.iceTransportPolicy,
            iceCandidatePoolSize: this.iceCandidatePoolSize,
        });

        const peerRecord = {
            targetUserId,
            pc,
            dc: null,
            sharedKey: null,
            remoteKeyId: null,
            nonceManager: new NonceManager(),
            e2eeStatus: this.e2eeEnabled ? 'exchanging' : 'disabled',
            connectionType: 'unknown', // 'direct' | 'relay' | 'unknown'
            localCandidateType: null,
            remoteCandidateType: null,
            dtlsState: 'new',
            dtlsVerified: false,
            dtlsCipher: null,
            srtpCipher: null,
            tlsVersion: null,
            sdpDtls: null,
            rttMs: null,
            makingOffer: false,
            ignoreOffer: false,
            isPolite: this.userId < targetUserId,
            pendingCandidates: [],
            reconnectAttempts: 0,
            reconnectTimer: null,
            pingTimer: null,
        };

        this.onSecurityEvent('peer_connection_started', { target_user_id: targetUserId });

        pc.onicecandidate = (event) => {
            if (event.candidate && this.signalSender) {
                this.signalSender(targetUserId, {
                    type: 'candidate',
                    candidate: event.candidate.toJSON ? event.candidate.toJSON() : event.candidate,
                });
            }
        };

        pc.oniceconnectionstatechange = async () => {
            const iceState = pc.iceConnectionState;
            console.log(`[SecureLink] ICE State with ${targetUserId}: ${iceState}`);

            if (iceState === 'connected' || iceState === 'completed') {
                peerRecord.reconnectAttempts = 0;
                this.clearReconnectTimer(peerRecord);
                await this.checkConnectionType(peerRecord);
                await this.verifyDtlsStatus(peerRecord);
            } else if (iceState === 'disconnected' || iceState === 'failed') {
                await this.verifyDtlsStatus(peerRecord);
                if (iceState === 'failed') {
                    this.onSecurityEvent('webrtc_failed', { target_user_id: targetUserId, ice_state: iceState });
                }
                this.scheduleReconnect(peerRecord);
            }

            this.notifyStatus();
        };

        pc.onconnectionstatechange = async () => {
            const state = pc.connectionState;
            if (state === 'connected') {
                peerRecord.reconnectAttempts = 0;
                this.clearReconnectTimer(peerRecord);
                await this.checkConnectionType(peerRecord);
                await this.verifyDtlsStatus(peerRecord);
                this.onSecurityEvent('webrtc_connected', {
                    target_user_id: targetUserId,
                    connection_type: peerRecord.connectionType,
                });
            } else if (state === 'failed') {
                await this.verifyDtlsStatus(peerRecord);
                this.onSecurityEvent('webrtc_failed', { target_user_id: targetUserId });
                this.scheduleReconnect(peerRecord);
            } else if (state === 'closed') {
                this.clearReconnectTimer(peerRecord);
                this.stopPingLoop(peerRecord);
            }
            this.notifyStatus();
        };

        pc.ondatachannel = (event) => {
            peerRecord.dc = event.channel;
            this.setupDataChannel(peerRecord);
        };

        this.peers.set(targetUserId, peerRecord);
        this.onPeerListChange(this.getDiagnostics().peers);
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
        if (!dc) return;

        dc.bufferedAmountLowThreshold = 65536;

        dc.onopen = async () => {
            console.log(`[SecureLink] DataChannel opened with ${peerRecord.targetUserId}`);
            peerRecord.reconnectAttempts = 0;
            this.clearReconnectTimer(peerRecord);

            await this.checkConnectionType(peerRecord);
            await this.verifyDtlsStatus(peerRecord);

            if (this.e2eeEnabled) {
                await this.cryptoReady;
                const bundle = await this.keyManager.getPublicKeyBundle();
                peerRecord.e2eeStatus = 'exchanging';
                if (dc.readyState === 'open') {
                    dc.send(JSON.stringify({
                        type: 'key-exchange',
                        senderId: this.userId,
                        keyId: bundle.keyId,
                        publicKey: bundle.publicJwk,
                    }));
                }
            }

            this.startPingLoop(peerRecord);
            this.notifyStatus();
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
                    this.notifyStatus();
                    return;
                }

                if (data.type === 'key-exchange-ack') {
                    peerRecord.peerAckedKey = true;
                    this.onSecurityStatusChange(this.getE2eeSummary());
                    this.notifyStatus();
                    return;
                }

                if (data.type === 'ping') {
                    if (dc.readyState === 'open') {
                        dc.send(JSON.stringify({ type: 'pong', ts: data.ts }));
                    }
                    return;
                }

                if (data.type === 'pong' && typeof data.ts === 'number') {
                    peerRecord.rttMs = Math.max(0, Math.round(performance.now() - data.ts));
                    this.onDiagnosticsUpdate(this.getDiagnostics());
                    return;
                }

                if (data.type === 'ack') {
                    this.handleAck(data.messageId);
                    this.onMessageAck(data.messageId);
                    this.onSecurityEvent('message_delivery_acknowledged', { message_id: data.messageId });
                    return;
                }

                if (data.type === 'reaction') {
                    this.onReactionReceived({
                        messageId: data.messageId,
                        emoji: data.emoji,
                        senderId: data.senderId,
                        senderName: data.senderName,
                    });
                    return;
                }

                if (data.type === 'delete') {
                    this.onMessageDeleted({
                        messageId: data.messageId,
                        senderId: data.senderId,
                    });
                    return;
                }

                if (data.type === 'chat') {
                    if (this.seenMessages.has(data.id)) {
                        dc.send(JSON.stringify({ type: 'ack', messageId: data.id }));
                        return;
                    }

                    this.seenMessages.add(data.id);
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
                        senderAvatarColor: data.senderAvatarColor || '#008069',
                        senderAvatarIcon: data.senderAvatarIcon || 'initial',
                        text,
                        timestamp: data.timestamp,
                        ephemeralDuration: data.ephemeralDuration,
                        replyTo: data.replyTo,
                        isE2EE: verifiedE2ee,
                        transport: 'p2p',
                    });
                }
            } catch (e) {
                console.error('[SecureLink] DataChannel message error:', e);
            }
        };

        dc.onerror = (err) => {
            console.warn(`[SecureLink] DataChannel error with ${peerRecord.targetUserId}:`, err);
        };

        dc.onclose = () => {
            console.log(`[SecureLink] DataChannel closed with ${peerRecord.targetUserId}`);
            this.stopPingLoop(peerRecord);
            this.notifyStatus();
        };
    }

    async initiateOffer(targetUserId, { iceRestart = false } = {}) {
        if (this.destroyed || targetUserId === this.userId) return;
        const peer = this.createPeerConnection(targetUserId);

        if (!peer.dc || peer.dc.readyState === 'closed') {
            peer.dc = peer.pc.createDataChannel('securelink-datachannel', { ordered: true });
            this.setupDataChannel(peer);
        }

        try {
            peer.makingOffer = true;
            const offer = await peer.pc.createOffer({ iceRestart });
            if (peer.pc.signalingState !== 'stable' && !iceRestart) {
                return;
            }
            await peer.pc.setLocalDescription(offer);

            if (this.signalSender) {
                this.signalSender(targetUserId, {
                    type: iceRestart ? 'ice-restart' : 'offer',
                    sdp: peer.pc.localDescription,
                });
            }
        } catch (err) {
            console.error(`[SecureLink] Failed to create offer for ${targetUserId}:`, err);
        } finally {
            peer.makingOffer = false;
        }
    }

    async handleSignal(fromUserId, signal) {
        if (this.destroyed || !fromUserId || fromUserId === this.userId || !signal) return;

        if (signal.type === 'bye') {
            this.removePeer(fromUserId);
            return;
        }

        const peer = this.createPeerConnection(fromUserId);
        const pc = peer.pc;

        try {
            if (signal.type === 'offer' || signal.type === 'ice-restart') {
                peer.sdpDtls = inspectSdpDtlsFingerprint(signal.sdp?.sdp);
                const offerCollision = peer.makingOffer || pc.signalingState !== 'stable';
                peer.ignoreOffer = !peer.isPolite && offerCollision;

                if (peer.ignoreOffer) {
                    console.warn(`[SecureLink] Ignoring colliding offer from ${fromUserId} (impolite peer)`);
                    return;
                }

                if (offerCollision) {
                    await Promise.all([
                        pc.setLocalDescription({ type: 'rollback' }),
                        pc.setRemoteDescription(new RTCSessionDescription(signal.sdp)),
                    ]);
                } else {
                    await pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                }

                await this.flushPendingCandidates(peer);

                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                this.onSecurityEvent('sdp_offer_exchanged', { target_user_id: fromUserId });

                if (this.signalSender) {
                    this.signalSender(fromUserId, {
                        type: 'answer',
                        sdp: pc.localDescription,
                    });
                }
            } else if (signal.type === 'answer') {
                peer.sdpDtls = inspectSdpDtlsFingerprint(signal.sdp?.sdp);
                if (pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                    await this.flushPendingCandidates(peer);
                    this.onSecurityEvent('sdp_answer_exchanged', { target_user_id: fromUserId });
                }
            } else if (signal.type === 'candidate' && signal.candidate) {
                if (pc.remoteDescription && pc.remoteDescription.type) {
                    await pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
                    this.onSecurityEvent('ice_candidate_exchanged', { target_user_id: fromUserId });
                } else {
                    peer.pendingCandidates.push(signal.candidate);
                }
            }
        } catch (err) {
            console.error(`[SecureLink] Signaling error (${signal.type}) from ${fromUserId}:`, err);
        }
    }

    async flushPendingCandidates(peer) {
        if (!peer.pendingCandidates.length) return;
        const queued = [...peer.pendingCandidates];
        peer.pendingCandidates = [];

        for (const candidate of queued) {
            try {
                await peer.pc.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (err) {
                console.warn('[SecureLink] Failed to flush queued ICE candidate:', err);
            }
        }
    }

    async checkConnectionType(peerRecord) {
        const pc = peerRecord?.pc;
        if (!pc) return;

        try {
            if (pc.sctp && pc.sctp.transport) {
                peerRecord.dtlsState = pc.sctp.transport.state || peerRecord.dtlsState;
                peerRecord.dtlsVerified = peerRecord.dtlsState === 'connected';
            }

            const stats = await pc.getStats();
            let selectedPair = null;

            stats.forEach((report) => {
                if (report.type === 'transport' && report.selectedCandidatePairId) {
                    selectedPair = stats.get(report.selectedCandidatePairId);
                }
            });

            if (!selectedPair) {
                stats.forEach((report) => {
                    if (report.type === 'candidate-pair' && report.state === 'succeeded' && report.nominated !== false) {
                        selectedPair = report;
                    }
                });
            }

            if (selectedPair) {
                const localCandidate = stats.get(selectedPair.localCandidateId);
                const remoteCandidate = stats.get(selectedPair.remoteCandidateId);

                peerRecord.localCandidateType = localCandidate?.candidateType || null;
                peerRecord.remoteCandidateType = remoteCandidate?.candidateType || null;

                if (typeof selectedPair.currentRoundTripTime === 'number') {
                    peerRecord.rttMs = Math.round(selectedPair.currentRoundTripTime * 1000);
                }

                const usesRelay =
                    peerRecord.localCandidateType === 'relay' ||
                    peerRecord.remoteCandidateType === 'relay';

                peerRecord.connectionType = usesRelay ? 'relay' : 'direct';
            }
        } catch (e) {
            // Stats might not be ready yet during early connection states
        }
    }

    startPingLoop(peerRecord) {
        this.stopPingLoop(peerRecord);
        peerRecord.pingTimer = setInterval(() => {
            if (peerRecord.dc && peerRecord.dc.readyState === 'open') {
                try {
                    peerRecord.dc.send(JSON.stringify({ type: 'ping', ts: performance.now() }));
                    this.checkConnectionType(peerRecord);
                    this.verifyDtlsStatus(peerRecord);
                } catch (e) {}
            }
        }, 8000);
    }

    stopPingLoop(peerRecord) {
        if (peerRecord.pingTimer) {
            clearInterval(peerRecord.pingTimer);
            peerRecord.pingTimer = null;
        }
    }

    clearReconnectTimer(peerRecord) {
        if (peerRecord.reconnectTimer) {
            clearTimeout(peerRecord.reconnectTimer);
            peerRecord.reconnectTimer = null;
        }
    }

    scheduleReconnect(peerRecord) {
        if (this.destroyed || peerRecord.reconnectTimer) return;
        if (peerRecord.reconnectAttempts >= this.maxReconnectAttempts) {
            console.warn(`[SecureLink] Max reconnect attempts reached for ${peerRecord.targetUserId}`);
            this.notifyStatus();
            return;
        }

        const attempt = peerRecord.reconnectAttempts;
        const delayMs = Math.min(12000, 1500 * Math.pow(2, attempt));
        peerRecord.reconnectAttempts++;

        console.log(`[SecureLink] Scheduling ICE restart for ${peerRecord.targetUserId} in ${delayMs}ms (attempt ${peerRecord.reconnectAttempts})`);

        peerRecord.reconnectTimer = setTimeout(() => {
            peerRecord.reconnectTimer = null;
            if (!this.destroyed && this.peers.has(peerRecord.targetUserId)) {
                this.initiateOffer(peerRecord.targetUserId, { iceRestart: true });
            }
        }, delayMs);
    }

    handleTransportReconnect() {
        if (this.destroyed) return;
        this.peers.forEach((peer) => {
            const iceState = peer.pc?.iceConnectionState;
            if (iceState !== 'connected' && iceState !== 'completed') {
                peer.reconnectAttempts = 0;
                this.onSecurityEvent('connection_reconnected', { target_user_id: peer.targetUserId });
                this.initiateOffer(peer.targetUserId, { iceRestart: true });
            }
        });
    }

    removePeer(targetUserId) {
        const peer = this.peers.get(targetUserId);
        if (!peer) return;

        this.clearReconnectTimer(peer);
        this.stopPingLoop(peer);

        try {
            if (peer.dc) peer.dc.close();
            if (peer.pc) peer.pc.close();
        } catch (e) {}

        this.peers.delete(targetUserId);
        this.notifyStatus();
    }

    notifyStatus() {
        this.onStatusChange(this.getOverallStatus());
        const diagnostics = this.getDiagnostics();
        this.onPeerListChange(diagnostics.peers);
        this.onDiagnosticsUpdate(diagnostics);
    }

    getOverallStatus() {
        let connectedCount = 0;
        let reconnectingCount = 0;
        let isRelay = false;

        this.peers.forEach((peer) => {
            if (peer.dc && peer.dc.readyState === 'open') {
                connectedCount++;
                if (peer.connectionType === 'relay') isRelay = true;
            } else if (peer.reconnectTimer || peer.pc?.iceConnectionState === 'checking' || peer.pc?.iceConnectionState === 'disconnected') {
                reconnectingCount++;
            }
        });

        if (connectedCount > 0) {
            return {
                status: 'connected',
                connectionType: isRelay ? 'relay' : 'direct',
                label: isRelay ? 'TURN Relay' : 'P2P Direct',
                color: isRelay ? 'text-amber-500' : 'text-emerald-500',
                count: connectedCount,
            };
        }

        if (reconnectingCount > 0) {
            return {
                status: 'reconnecting',
                connectionType: 'unknown',
                label: 'Menyambungkan Ulang...',
                color: 'text-amber-500',
                count: 0,
            };
        }

        return {
            status: 'waiting',
            connectionType: 'unknown',
            label: 'Menunggu Peer...',
            color: 'text-blue-500',
            count: 0,
        };
    }

    getDiagnostics() {
        const peers = [];
        this.peers.forEach((peer) => {
            peers.push({
                userId: peer.targetUserId,
                connectionType: peer.connectionType,
                localCandidateType: peer.localCandidateType,
                remoteCandidateType: peer.remoteCandidateType,
                iceState: peer.pc?.iceConnectionState || 'new',
                connectionState: peer.pc?.connectionState || 'new',
                dataChannelState: peer.dc?.readyState || 'closed',
                dtlsState: peer.dtlsState,
                dtlsVerified: Boolean(peer.dtlsVerified),
                e2eeReady: Boolean(peer.sharedKey),
                e2eeStatus: peer.e2eeStatus,
                rttMs: peer.rttMs,
            });
        });

        return {
            overall: this.getOverallStatus(),
            hasTurn: this.hasTurn,
            iceTransportPolicy: this.iceTransportPolicy,
            peers,
        };
    }

    async broadcastMessage(text, ephemeralDuration = 0, replyTo = null) {
        const messageId = 'msg_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const sendToPeers = async () => {
            const promises = Array.from(this.peers.values()).map(async (peer) => {
                if (!peer.dc || peer.dc.readyState !== 'open') return false;

                try {
                    const { ciphertext, iv, encrypted, algorithm } = await this.encryptText(text, peer, messageId);
                    const payload = JSON.stringify({
                        type: 'chat',
                        id: messageId,
                        senderId: this.userId,
                        senderName: this.userName,
                        senderAvatarColor: this.userAvatarColor,
                        senderAvatarIcon: this.userAvatarIcon,
                        ciphertext,
                        iv,
                        encrypted,
                        algorithm: algorithm || null,
                        timestamp,
                        ephemeralDuration,
                        replyTo,
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

            return Promise.all(promises);
        };

        await sendToPeers();

        const record = {
            id: messageId,
            text,
            ephemeralDuration,
            replyTo,
            timestamp,
            delivered: false,
            retries: 0,
            retryTimer: null,
        };
        this.messageStore.set(messageId, record);

        const scheduleRetry = () => {
            if (this.destroyed || record.delivered || record.retries >= this.maxMessageRetries) {
                return;
            }
            const delay = 1500 * Math.pow(2, record.retries);
            record.retryTimer = setTimeout(async () => {
                if (this.destroyed || record.delivered) return;
                record.retries++;
                await sendToPeers();
                scheduleRetry();
            }, delay);
        };

        if (this.peers.size > 0) {
            scheduleRetry();
        }

        return {
            id: messageId,
            timestamp,
            text,
            ephemeralDuration,
            replyTo,
        };
    }

    getSecurityInfo() {
        const dtls = this.getDtlsSummary();
        const e2ee = this.getE2eeSummary();
        const overall = this.getOverallStatus();
        const peers = Array.from(this.peers.values());

        const openPeers = peers.filter((p) => p.dc && p.dc.readyState === 'open').length;
        const anyRelay = peers.some((p) => p.dc?.readyState === 'open' && p.connectionType === 'relay');
        const anyDirect = peers.some((p) => p.dc?.readyState === 'open' && p.connectionType === 'direct');
        const connectionType = anyRelay ? 'relay' : (anyDirect ? 'direct' : 'unknown');

        const webrtcStates = peers.map((p) => p.pc?.connectionState || 'new');
        const iceStates = peers.map((p) => p.pc?.iceConnectionState || 'new');
        const webrtcState = webrtcStates.includes('connected')
            ? 'connected'
            : (webrtcStates.includes('connecting') ? 'connecting' : (webrtcStates.includes('failed') ? 'failed' : (webrtcStates[0] || 'new')));
        const iceState = iceStates.includes('connected') || iceStates.includes('completed')
            ? 'connected'
            : (iceStates.includes('checking') ? 'checking' : (iceStates.includes('failed') ? 'failed' : (iceStates[0] || 'new')));
        const dataChannelState = openPeers > 0 ? 'open' : (peers.some((p) => p.dc?.readyState === 'connecting') ? 'connecting' : 'closed');

        let compactIndicator = 'Disconnected';
        if (dtls.evaluation.secureConnection) {
            compactIndicator = 'Secure Connection';
        } else if (webrtcState === 'connecting' || iceState === 'checking' || dataChannelState === 'connecting') {
            compactIndicator = 'Connecting';
        } else if (iceState === 'disconnected') {
            compactIndicator = 'Reconnecting';
        }

        return {
            connectionType,
            webrtcState,
            iceState,
            dataChannelState,
            dtlsState: dtls.dtlsState,
            dtlsVerified: dtls.dtlsVerified,
            dtlsCipher: dtls.dtlsCipher,
            srtpCipher: dtls.srtpCipher,
            tlsVersion: dtls.tlsVersion,
            e2eeEnabled: e2ee.enabled,
            e2eeStatus: e2ee.status,
            e2eeActive: dtls.evaluation.e2eeActive,
            e2eeAlgorithm: e2ee.algorithm,
            signalingStatus: this.signalSender ? 'ready' : 'unavailable',
            connectedPeers: openPeers,
            totalPeers: peers.length,
            secureConnection: dtls.evaluation.secureConnection,
            compactIndicator,
            overallLabel: overall.label,
            vpnNote: 'Status VPN Tailscale tidak dapat diverifikasi langsung oleh browser; gunakan halaman diagnostik.',
        };
    }

    sendReaction(messageId, emoji) {
        if (!messageId || !emoji) return;
        const payload = JSON.stringify({
            type: 'reaction',
            messageId,
            emoji,
            senderId: this.userId,
            senderName: this.userName,
        });
        this.peers.forEach((peer) => {
            if (peer.dc && peer.dc.readyState === 'open') {
                peer.dc.send(payload);
            }
        });
    }

    sendDelete(messageId) {
        if (!messageId) return;
        const payload = JSON.stringify({
            type: 'delete',
            messageId,
            senderId: this.userId,
        });
        this.peers.forEach((peer) => {
            if (peer.dc && peer.dc.readyState === 'open') {
                peer.dc.send(payload);
            }
        });
    }

    handleAck(messageId) {
        const item = this.messageStore.get(messageId);
        if (item) {
            item.delivered = true;
            if (item.retryTimer) {
                clearTimeout(item.retryTimer);
                item.retryTimer = null;
            }
        }
    }

    destroy() {
        this.destroyed = true;
        this.keyManager.destroy();
        this.messageStore.forEach((item) => {
            if (item.retryTimer) clearTimeout(item.retryTimer);
        });
        this.messageStore.clear();

        Array.from(this.peers.keys()).forEach((peerId) => {
            if (this.signalSender) {
                try {
                    this.signalSender(peerId, { type: 'bye' });
                } catch (e) {}
            }
            this.removePeer(peerId);
        });
    }
}
