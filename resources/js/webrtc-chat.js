/**
 * SecureLink WebRTC & E2EE Client Engine
 * Implements PRD Section 3.4, 4.1, 6.1-6.3, and 12.1.
 *
 * Features:
 * - Dynamic STUN/TURN ICE server configuration
 * - RTCPeerConnection with polite/impolite glare-free SDP Offer/Answer exchange
 * - ICE Candidate buffering queue until remoteDescription is set
 * - RTCDataChannel with ACK, exponential retry, and ping/pong RTT measurement
 * - Web Crypto API (ECDH P-256 + AES-GCM 256-bit E2EE)
 * - Honest connection detection (Direct P2P vs TURN Relay via RTCIceCandidatePairStats)
 * - Automatic disconnect cleanup and ICE-restart reconnection
 */

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
        this.onStatusChange = config.onStatusChange || (() => {});
        this.onPeerListChange = config.onPeerListChange || (() => {});
        this.onMessageAck = config.onMessageAck || (() => {});
        this.onDiagnosticsUpdate = config.onDiagnosticsUpdate || (() => {});
        this.signalSender = config.signalSender || null;

        this.peers = new Map();
        this.messageStore = new Map();
        this.seenMessages = new Set();
        this.localKeyPair = null;
        this.destroyed = false;

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

        this.initCrypto();
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
        if (!window.crypto || !window.crypto.subtle) {
            console.warn('[SecureLink] Web Crypto API not available in this environment');
            return;
        }

        try {
            this.localKeyPair = await window.crypto.subtle.generateKey(
                { name: 'ECDH', namedCurve: 'P-256' },
                true,
                ['deriveKey', 'deriveBits']
            );
        } catch (e) {
            console.error('[SecureLink] Failed to generate ECDH keys:', e);
        }
    }

    async exportPublicKey() {
        if (!this.localKeyPair) await this.initCrypto();
        if (!this.localKeyPair) return null;
        return await window.crypto.subtle.exportKey('jwk', this.localKeyPair.publicKey);
    }

    async deriveSharedKey(remoteJwkKey) {
        if (!remoteJwkKey || !this.localKeyPair) return null;
        try {
            const remoteKey = await window.crypto.subtle.importKey(
                'jwk',
                remoteJwkKey,
                { name: 'ECDH', namedCurve: 'P-256' },
                true,
                []
            );

            return await window.crypto.subtle.deriveKey(
                { name: 'ECDH', public: remoteKey },
                this.localKeyPair.privateKey,
                { name: 'AES-GCM', length: 256 },
                false,
                ['encrypt', 'decrypt']
            );
        } catch (err) {
            console.error('[SecureLink] Key derivation error:', err);
            return null;
        }
    }

    bytesToBase64(bytes) {
        let binary = '';
        const chunkSize = 0x8000;
        for (let i = 0; i < bytes.length; i += chunkSize) {
            binary += String.fromCharCode(...bytes.subarray(i, i + chunkSize));
        }
        return btoa(binary);
    }

    base64ToBytes(b64) {
        const binary = atob(b64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes;
    }

    async encryptText(text, sharedKey) {
        const encoded = new TextEncoder().encode(text);
        if (!sharedKey || !this.e2eeEnabled) {
            return { ciphertext: this.bytesToBase64(encoded), iv: null, encrypted: false };
        }

        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const encrypted = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv },
            sharedKey,
            encoded
        );

        return {
            ciphertext: this.bytesToBase64(new Uint8Array(encrypted)),
            iv: this.bytesToBase64(iv),
            encrypted: true,
        };
    }

    async decryptPayload(ciphertextB64, ivB64, sharedKey) {
        if (!sharedKey || !ivB64) {
            return new TextDecoder().decode(this.base64ToBytes(ciphertextB64));
        }

        try {
            const ciphertext = this.base64ToBytes(ciphertextB64);
            const iv = this.base64ToBytes(ivB64);

            const decrypted = await window.crypto.subtle.decrypt(
                { name: 'AES-GCM', iv },
                sharedKey,
                ciphertext
            );

            return new TextDecoder().decode(decrypted);
        } catch (err) {
            console.error('[SecureLink] Decryption failed:', err);
            return '[Pesan gagal didekripsi: integritas rusak]';
        }
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
            connectionType: 'unknown', // 'direct' | 'relay' | 'unknown'
            localCandidateType: null,
            remoteCandidateType: null,
            dtlsState: 'new',
            rttMs: null,
            makingOffer: false,
            ignoreOffer: false,
            isPolite: this.userId < targetUserId,
            pendingCandidates: [],
            reconnectAttempts: 0,
            reconnectTimer: null,
            pingTimer: null,
        };

        pc.onicecandidate = (event) => {
            if (event.candidate && this.signalSender) {
                this.signalSender(targetUserId, {
                    type: 'candidate',
                    candidate: event.candidate.toJSON ? event.candidate.toJSON() : event.candidate,
                });
            }
        };

        pc.oniceconnectionstatechange = () => {
            const iceState = pc.iceConnectionState;
            console.log(`[SecureLink] ICE State with ${targetUserId}: ${iceState}`);

            if (iceState === 'connected' || iceState === 'completed') {
                peerRecord.reconnectAttempts = 0;
                this.clearReconnectTimer(peerRecord);
                this.checkConnectionType(peerRecord);
            } else if (iceState === 'disconnected' || iceState === 'failed') {
                this.scheduleReconnect(peerRecord);
            }

            this.notifyStatus();
        };

        pc.onconnectionstatechange = () => {
            const state = pc.connectionState;
            if (state === 'connected') {
                peerRecord.reconnectAttempts = 0;
                this.clearReconnectTimer(peerRecord);
                this.checkConnectionType(peerRecord);
            } else if (state === 'failed') {
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

    setupDataChannel(peerRecord) {
        const dc = peerRecord.dc;
        if (!dc) return;

        dc.bufferedAmountLowThreshold = 65536;

        dc.onopen = async () => {
            console.log(`[SecureLink] DataChannel opened with ${peerRecord.targetUserId}`);
            peerRecord.reconnectAttempts = 0;
            this.clearReconnectTimer(peerRecord);

            const pubKey = await this.exportPublicKey();
            if (pubKey && dc.readyState === 'open') {
                dc.send(JSON.stringify({
                    type: 'key-exchange',
                    senderId: this.userId,
                    publicKey: pubKey,
                }));
            }

            await this.checkConnectionType(peerRecord);
            this.startPingLoop(peerRecord);
            this.notifyStatus();
        };

        dc.onmessage = async (event) => {
            try {
                const data = JSON.parse(event.data);

                if (data.type === 'key-exchange') {
                    peerRecord.sharedKey = await this.deriveSharedKey(data.publicKey);
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
                    return;
                }

                if (data.type === 'chat') {
                    if (this.seenMessages.has(data.id)) {
                        dc.send(JSON.stringify({ type: 'ack', messageId: data.id }));
                        return;
                    }

                    this.seenMessages.add(data.id);
                    dc.send(JSON.stringify({ type: 'ack', messageId: data.id }));

                    const text = await this.decryptPayload(data.ciphertext, data.iv, peerRecord.sharedKey);
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
                        isE2EE: !!data.iv,
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

                if (this.signalSender) {
                    this.signalSender(fromUserId, {
                        type: 'answer',
                        sdp: pc.localDescription,
                    });
                }
            } else if (signal.type === 'answer') {
                if (pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                    await this.flushPendingCandidates(peer);
                }
            } else if (signal.type === 'candidate' && signal.candidate) {
                if (pc.remoteDescription && pc.remoteDescription.type) {
                    await pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
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
                e2eeReady: Boolean(peer.sharedKey),
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

                const { ciphertext, iv } = await this.encryptText(text, peer.sharedKey);
                const payload = JSON.stringify({
                    type: 'chat',
                    id: messageId,
                    senderId: this.userId,
                    senderName: this.userName,
                    senderAvatarColor: this.userAvatarColor,
                    senderAvatarIcon: this.userAvatarIcon,
                    ciphertext,
                    iv,
                    timestamp,
                    ephemeralDuration,
                    replyTo,
                });

                peer.dc.send(payload);
                return true;
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
