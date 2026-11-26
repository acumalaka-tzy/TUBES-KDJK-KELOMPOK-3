import test from 'node:test';
import assert from 'node:assert/strict';

import {
    E2EEKeyManager,
    NonceManager,
    bytesToBase64,
    base64ToBytes,
    sanitizePublicJwk,
    deriveSharedSecretFromJwk,
    deriveAesGcmKeyWithHkdf,
    derivePeerEncryptionKey,
    encryptAesGcm,
    decryptAesGcm,
} from '../../resources/js/e2ee-crypto.js';
import {
    inspectSdpDtlsFingerprint,
    verifyPeerDtlsTransport,
    evaluateTransportAndE2eeSecurity,
} from '../../resources/js/dtls-verifier.js';
import { SecureLinkPeer } from '../../resources/js/webrtc-chat.js';

test('KeyPair: generates non-extractable private key, sanitized public JWK, and supports rotation', async () => {
    const manager = new E2EEKeyManager();
    const initial = await manager.initialize();

    assert.equal(initial.keyPair.privateKey.extractable, false);
    assert.equal(initial.keyPair.publicKey.extractable, true);
    assert.equal(initial.publicJwk.kty, 'EC');
    assert.equal(initial.publicJwk.crv, 'P-256');
    assert.equal('d' in initial.publicJwk, false);

    assert.throws(
        () => sanitizePublicJwk({ ...initial.publicJwk, d: 'leak' }),
        /private key/i
    );

    const rotated = await manager.rotateKeyPair();
    assert.notEqual(rotated.keyId, initial.keyId);
    assert.equal(manager.getStatus().rotationCount, 1);
});

test('ECDH: derives identical 256-bit shared secrets for both peers and rejects self-exchange', async () => {
    const alice = new E2EEKeyManager();
    const bob = new E2EEKeyManager();
    const ka = await alice.initialize();
    const kb = await bob.initialize();

    const secretA = await deriveSharedSecretFromJwk(ka.keyPair, kb.publicJwk);
    const secretB = await deriveSharedSecretFromJwk(kb.keyPair, ka.publicJwk);

    assert.equal(secretA.sharedSecret.length, 32);
    assert.deepEqual(secretA.sharedSecret, secretB.sharedSecret);

    await assert.rejects(
        () => deriveSharedSecretFromJwk(ka.keyPair, ka.publicJwk),
        /identik/i
    );
});

test('HKDF: derives non-extractable AES-GCM-256 key symmetrically and isolates different room contexts', async () => {
    const alice = new E2EEKeyManager();
    const bob = new E2EEKeyManager();
    const ka = await alice.initialize();
    const kb = await bob.initialize();

    const keyA = await derivePeerEncryptionKey(ka.keyPair, kb.publicJwk, {
        roomId: 'SEC-ROOM-A',
        localUserId: 'usr_alice',
        remoteUserId: 'usr_bob',
        extraSalt: 'epoch-1',
    });
    const keyB = await derivePeerEncryptionKey(kb.keyPair, ka.publicJwk, {
        roomId: 'SEC-ROOM-A',
        localUserId: 'usr_bob',
        remoteUserId: 'usr_alice',
        extraSalt: 'epoch-1',
    });
    const keyOtherRoom = await derivePeerEncryptionKey(kb.keyPair, ka.publicJwk, {
        roomId: 'SEC-ROOM-B',
        localUserId: 'usr_bob',
        remoteUserId: 'usr_alice',
        extraSalt: 'epoch-1',
    });

    assert.equal(keyA.aesKey.extractable, false);
    assert.equal(keyA.aesKey.algorithm.name, 'AES-GCM');
    assert.equal(keyA.aesKey.algorithm.length, 256);

    const nmA = new NonceManager();
    const nmB = new NonceManager();
    const enc = await encryptAesGcm('HKDF domain test', keyA.aesKey, nmA);
    assert.equal(await decryptAesGcm(enc, keyB.aesKey, nmB), 'HKDF domain test');

    await assert.rejects(
        () => decryptAesGcm(enc, keyOtherRoom.aesKey, new NonceManager()),
        /gagal/i
    );

    await assert.rejects(
        () => deriveAesGcmKeyWithHkdf(new Uint8Array(16), { roomId: 'R', localUserId: 'a', remoteUserId: 'b' }),
        /minimal 32 byte/i
    );
});

test('Nonce/IV: generates unique 96-bit IVs and blocks replay and invalid lengths', () => {
    const nm = new NonceManager();
    const seen = new Set();

    for (let i = 0; i < 500; i++) {
        const iv = nm.nextIv();
        assert.equal(iv.length, 12);
        const encoded = bytesToBase64(iv);
        assert.equal(seen.has(encoded), false);
        seen.add(encoded);
    }

    const sampleIv = seen.values().next().value;
    nm.registerIncomingIv(sampleIv);

    assert.throws(() => nm.registerIncomingIv(sampleIv), /replay/i);
    assert.throws(() => nm.registerIncomingIv(new Uint8Array(8)), /12 byte/i);

    nm.reset();
    assert.equal(nm.getStats().outgoingCount, '0');
});

test('AES-GCM: supports Unicode & large payloads and rejects ciphertext tampering, IV tampering, AAD mismatch, and wrong keys', async () => {
    const alice = new E2EEKeyManager();
    const bob = new E2EEKeyManager();
    const mallory = new E2EEKeyManager();
    const ka = await alice.initialize();
    const kb = await bob.initialize();
    const km = await mallory.initialize();

    const { aesKey: aliceKey } = await derivePeerEncryptionKey(ka.keyPair, kb.publicJwk, {
        roomId: 'SEC-CHAT-1',
        localUserId: 'usr_a',
        remoteUserId: 'usr_b',
    });
    const { aesKey: bobKey } = await derivePeerEncryptionKey(kb.keyPair, ka.publicJwk, {
        roomId: 'SEC-CHAT-1',
        localUserId: 'usr_b',
        remoteUserId: 'usr_a',
    });
    const { aesKey: malloryKey } = await derivePeerEncryptionKey(km.keyPair, ka.publicJwk, {
        roomId: 'SEC-CHAT-1',
        localUserId: 'usr_m',
        remoteUserId: 'usr_a',
    });

    const nmA = new NonceManager();
    const nmB = new NonceManager();
    const aad = { roomId: 'SEC-CHAT-1', senderId: 'usr_a', messageId: 'msg_100' };
    const longUnicode = 'Halo SecureLink 🔐✨ ' + 'DataPrivat-'.repeat(1200);

    const enc = await encryptAesGcm(longUnicode, aliceKey, nmA, aad);
    assert.equal(enc.encrypted, true);
    assert.equal(enc.tagLength, 128);

    // Valid round-trip
    assert.equal(await decryptAesGcm(enc, bobKey, nmB, aad), longUnicode);

    // Tampered ciphertext (flip last byte in auth tag)
    const ctBytes = base64ToBytes(enc.ciphertext);
    ctBytes[ctBytes.length - 1] ^= 0xff;
    await assert.rejects(
        () => decryptAesGcm({ ...enc, ciphertext: bytesToBase64(ctBytes) }, bobKey, new NonceManager(), aad),
        /gagal/i
    );

    // Tampered IV
    const ivBytes = base64ToBytes(enc.iv);
    ivBytes[0] ^= 0x01;
    await assert.rejects(
        () => decryptAesGcm({ ...enc, iv: bytesToBase64(ivBytes) }, bobKey, new NonceManager(), aad),
        /gagal/i
    );

    // Cross-room AAD mismatch
    await assert.rejects(
        () => decryptAesGcm(enc, bobKey, new NonceManager(), { ...aad, roomId: 'SEC-OTHER' }),
        /gagal/i
    );

    // Wrong key
    await assert.rejects(
        () => decryptAesGcm(enc, malloryKey, new NonceManager(), aad),
        /gagal/i
    );
});

test('SecureLinkPeer: enforces E2EE (rejects plaintext fallback & downgrade) and verifies DTLS separately from E2EE', async () => {
    const alice = new SecureLinkPeer({ roomId: 'SEC-LIVE-1', userId: 'usr_a', userName: 'Alice' });
    const bob = new SecureLinkPeer({ roomId: 'SEC-LIVE-1', userId: 'usr_b', userName: 'Bob' });
    await alice.cryptoReady;
    await bob.cryptoReady;

    // Attempting encryption without derived peer key must throw rather than leaking plaintext
    await assert.rejects(
        () => alice.encryptText('Must not leak', { targetUserId: 'usr_b', sharedKey: null, nonceManager: new NonceManager() }, 'msg_0'),
        /dibatalkan/i
    );

    const pubA = await alice.exportPublicKey();
    const pubB = await bob.exportPublicKey();

    const peerRecordA = { targetUserId: 'usr_b', sharedKey: null, nonceManager: new NonceManager(), e2eeStatus: 'exchanging' };
    const peerRecordB = { targetUserId: 'usr_a', sharedKey: null, nonceManager: new NonceManager(), e2eeStatus: 'exchanging' };

    await alice.deriveSharedKey(pubB, 'usr_b', peerRecordA);
    await bob.deriveSharedKey(pubA, 'usr_a', peerRecordB);

    const enc = await alice.encryptText('Pesan terenkripsi P2P', peerRecordA, 'msg_1');
    const dec = await bob.decryptPayload(enc.ciphertext, enc.iv, peerRecordB, {
        encrypted: true,
        aadContext: { roomId: 'SEC-LIVE-1', senderId: 'usr_a', messageId: 'msg_1' },
    });
    assert.equal(dec, 'Pesan terenkripsi P2P');

    // Downgrade attack attempt (iv: null, encrypted: false) must be rejected
    const downgrade = await bob.decryptPayload(bytesToBase64(new TextEncoder().encode('forged')), null, peerRecordB, {
        encrypted: false,
    });
    assert.match(downgrade, /ditolak/i);

    // DTLS verification checks transport state accurately and keeps DTLS separate from E2EE
    const sdpCheck = inspectSdpDtlsFingerprint('v=0\r\na=fingerprint:sha-256 12:34:56\r\na=setup:active\r\n');
    assert.equal(sdpCheck.hasFingerprint, true);
    assert.equal(sdpCheck.setup, 'active');

    const connectingPc = {
        iceConnectionState: 'checking',
        connectionState: 'connecting',
        sctp: { transport: { state: 'connecting' } },
        async getStats() {
            return new Map([['t', { type: 'transport', dtlsState: 'connecting' }]]);
        },
    };
    const dtlsConnecting = await verifyPeerDtlsTransport(connectingPc);
    assert.equal(dtlsConnecting.dtlsVerified, false);

    const relayPc = {
        iceConnectionState: 'connected',
        connectionState: 'connected',
        sctp: { transport: { state: 'connected' } },
        async getStats() {
            return new Map([
                ['t', { type: 'transport', dtlsState: 'connected', dtlsCipher: 'TLS_AES_256_GCM_SHA384', selectedCandidatePairId: 'cp1' }],
                ['cp1', { type: 'candidate-pair', state: 'succeeded', localCandidateId: 'lc1' }],
                ['lc1', { candidateType: 'relay' }],
            ]);
        },
    };
    const dtlsRelay = await verifyPeerDtlsTransport(relayPc);
    assert.equal(dtlsRelay.dtlsVerified, true);
    assert.equal(dtlsRelay.connectionType, 'relay');

    const separation = evaluateTransportAndE2eeSecurity({
        dtlsState: 'connected',
        e2eeStatus: 'exchanging',
        e2eeEnabled: true,
        dataChannelState: 'open',
    });
    assert.equal(separation.dtlsConnected, true);
    assert.equal(separation.e2eeActive, false);
    assert.equal(separation.secureConnection, false);
});
