/**
 * SecureLink End-to-End Encryption (E2EE) Cryptography Module
 * Implements PRD Section 4.1 (Key Pair, ECDH, HKDF, AES-GCM, Nonce/IV Lifecycle).
 */

export const E2EE_CURVE = 'P-256';
export const AES_GCM_IV_LENGTH = 12; // 96-bit IV recommended for AES-GCM (NIST SP 800-38D)
export const AES_GCM_TAG_LENGTH = 128; // 128-bit authentication tag
export const MAX_NONCE_HISTORY = 10000;

/**
 * Resolve Web Crypto SubtleCrypto across browser and Node.js runtimes.
 * @returns {SubtleCrypto}
 */
export function getSubtleCrypto() {
    const subtle = globalThis.crypto?.subtle;
    if (!subtle) {
        throw new Error('Web Crypto API (SubtleCrypto) tidak tersedia pada lingkungan ini.');
    }
    return subtle;
}

/**
 * Resolve Crypto interface for CSPRNG operations.
 * @returns {Crypto}
 */
export function getWebCrypto() {
    const webCrypto = globalThis.crypto;
    if (!webCrypto || typeof webCrypto.getRandomValues !== 'function') {
        throw new Error('Web Crypto CSPRNG tidak tersedia pada lingkungan ini.');
    }
    return webCrypto;
}

/**
 * Encode raw bytes to standard Base64 without spread-operator stack limits.
 * @param {Uint8Array} bytes
 * @returns {string}
 */
export function bytesToBase64(bytes) {
    let binary = '';
    const chunkSize = 0x8000;
    for (let i = 0; i < bytes.length; i += chunkSize) {
        binary += String.fromCharCode(...bytes.subarray(i, i + chunkSize));
    }
    return btoa(binary);
}

/**
 * Decode standard Base64 string to Uint8Array.
 * @param {string} base64
 * @returns {Uint8Array}
 */
export function base64ToBytes(base64) {
    if (typeof base64 !== 'string' || base64.length === 0) {
        throw new Error('Input Base64 tidak valid.');
    }
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return bytes;
}

/**
 * Compute deterministic SHA-256 hex key identifier from a public JWK.
 * Used strictly for key lifecycle / rotation tracking (not identity authentication).
 * @param {JsonWebKey} jwk
 * @returns {Promise<string>}
 */
export async function computePublicKeyId(jwk) {
    if (!jwk || jwk.kty !== 'EC' || jwk.crv !== E2EE_CURVE || !jwk.x || !jwk.y) {
        throw new Error('JWK public key ECDH P-256 tidak valid.');
    }

    const canonical = JSON.stringify({
        crv: jwk.crv,
        kty: jwk.kty,
        x: jwk.x,
        y: jwk.y,
    });
    const digest = await getSubtleCrypto().digest(
        'SHA-256',
        new TextEncoder().encode(canonical)
    );
    const hashBytes = new Uint8Array(digest);
    return Array.from(hashBytes.slice(0, 8), (b) => b.toString(16).padStart(2, '0')).join('');
}

/**
 * Validate and sanitize a public ECDH P-256 JWK, ensuring no private key parameter is present.
 * @param {JsonWebKey} jwk
 * @returns {JsonWebKey}
 */
export function sanitizePublicJwk(jwk) {
    if (!jwk || typeof jwk !== 'object') {
        throw new Error('Public key JWK harus berupa objek.');
    }
    if ('d' in jwk && jwk.d !== undefined) {
        throw new Error('Key JWK tidak boleh mengandung komponen private key (d).');
    }
    if (jwk.kty !== 'EC' || jwk.crv !== E2EE_CURVE || typeof jwk.x !== 'string' || typeof jwk.y !== 'string') {
        throw new Error('Public key JWK harus menggunakan kurva ECDH P-256 dengan koordinat x dan y.');
    }

    return {
        kty: 'EC',
        crv: E2EE_CURVE,
        x: jwk.x,
        y: jwk.y,
        ext: true,
    };
}

/**
 * Generate a session ECDH P-256 Public/Private Key Pair.
 * Private key is marked non-extractable so it can never be exported in plain-text.
 * @returns {Promise<CryptoKeyPair>}
 */
export async function generateEcdhKeyPair() {
    return await getSubtleCrypto().generateKey(
        {
            name: 'ECDH',
            namedCurve: E2EE_CURVE,
        },
        false,
        ['deriveKey', 'deriveBits']
    );
}

/**
 * Export public key from a CryptoKeyPair as sanitized JWK.
 * @param {CryptoKey} publicKey
 * @returns {Promise<JsonWebKey>}
 */
export async function exportPublicKeyJwk(publicKey) {
    if (!publicKey || publicKey.type !== 'public') {
        throw new Error('Objek kunci bukan public key yang valid.');
    }
    const jwk = await getSubtleCrypto().exportKey('jwk', publicKey);
    return sanitizePublicJwk(jwk);
}

/**
 * Import a peer's public ECDH P-256 JWK as a CryptoKey.
 * @param {JsonWebKey} remoteJwk
 * @returns {Promise<CryptoKey>}
 */
export async function importRemotePublicKeyJwk(remoteJwk) {
    const cleanJwk = sanitizePublicJwk(remoteJwk);
    return await getSubtleCrypto().importKey(
        'jwk',
        cleanJwk,
        {
            name: 'ECDH',
            namedCurve: E2EE_CURVE,
        },
        true,
        []
    );
}

/**
 * Manages the E2EE Public/Private Key Pair lifecycle for a client session.
 */
export class E2EEKeyManager {
    constructor() {
        this.keyPair = null;
        this.publicJwk = null;
        this.keyId = null;
        this.createdAt = null;
        this.rotationCount = 0;
    }

    /**
     * Initialize or return the active session key pair.
     * @returns {Promise<{keyPair: CryptoKeyPair, publicJwk: JsonWebKey, keyId: string}>}
     */
    async initialize() {
        if (this.keyPair && this.publicJwk && this.keyId) {
            return {
                keyPair: this.keyPair,
                publicJwk: this.publicJwk,
                keyId: this.keyId,
            };
        }

        const keyPair = await generateEcdhKeyPair();
        const publicJwk = await exportPublicKeyJwk(keyPair.publicKey);
        const keyId = await computePublicKeyId(publicJwk);

        this.keyPair = keyPair;
        this.publicJwk = publicJwk;
        this.keyId = keyId;
        this.createdAt = new Date().toISOString();

        return {
            keyPair: this.keyPair,
            publicJwk: this.publicJwk,
            keyId: this.keyId,
        };
    }

    /**
     * Export the active public key JWK (initializes if needed).
     * @returns {Promise<{publicJwk: JsonWebKey, keyId: string}>}
     */
    async getPublicKeyBundle() {
        const state = await this.initialize();
        return {
            publicJwk: state.publicJwk,
            keyId: state.keyId,
        };
    }

    /**
     * Rotate key pair when security context or session changes.
     * @returns {Promise<{keyPair: CryptoKeyPair, publicJwk: JsonWebKey, keyId: string}>}
     */
    async rotateKeyPair() {
        this.destroy();
        this.rotationCount += 1;
        return await this.initialize();
    }

    /**
     * Clear active key references from memory.
     */
    destroy() {
        this.keyPair = null;
        this.publicJwk = null;
        this.keyId = null;
        this.createdAt = null;
    }

    /**
     * Return safe diagnostic metadata (never exposes private key material).
     */
    getStatus() {
        return {
            initialized: Boolean(this.keyPair && this.keyId),
            curve: E2EE_CURVE,
            keyId: this.keyId,
            privateKeyExtractable: this.keyPair ? this.keyPair.privateKey.extractable : null,
            createdAt: this.createdAt,
            rotationCount: this.rotationCount,
        };
    }
}

/**
 * Derive a 256-bit ECDH shared secret from local private key and peer public key.
 * @param {CryptoKey} localPrivateKey
 * @param {CryptoKey} remotePublicKey
 * @returns {Promise<Uint8Array>}
 */
export async function deriveEcdhSharedSecretBits(localPrivateKey, remotePublicKey) {
    if (!localPrivateKey || localPrivateKey.type !== 'private' || localPrivateKey.algorithm?.name !== 'ECDH') {
        throw new Error('Local private key ECDH tidak valid.');
    }
    if (!remotePublicKey || remotePublicKey.type !== 'public' || remotePublicKey.algorithm?.name !== 'ECDH') {
        throw new Error('Remote public key ECDH tidak valid.');
    }

    const bits = await getSubtleCrypto().deriveBits(
        {
            name: 'ECDH',
            public: remotePublicKey,
        },
        localPrivateKey,
        256
    );

    const secretBytes = new Uint8Array(bits);
    if (secretBytes.length !== 32) {
        throw new Error('Panjang shared secret ECDH tidak sesuai (harus 256-bit).');
    }
    return secretBytes;
}

/**
 * Validate remote JWK, ensure it differs from local public key, and derive ECDH shared secret bits.
 * @param {CryptoKeyPair} localKeyPair
 * @param {JsonWebKey} remoteJwk
 * @returns {Promise<{sharedSecret: Uint8Array, remoteKeyId: string}>}
 */
export async function deriveSharedSecretFromJwk(localKeyPair, remoteJwk) {
    if (!localKeyPair?.privateKey || !localKeyPair?.publicKey) {
        throw new Error('Key pair lokal belum diinisialisasi.');
    }

    const cleanRemoteJwk = sanitizePublicJwk(remoteJwk);
    const localPublicJwk = await exportPublicKeyJwk(localKeyPair.publicKey);
    const localKeyId = await computePublicKeyId(localPublicJwk);
    const remoteKeyId = await computePublicKeyId(cleanRemoteJwk);

    if (localKeyId === remoteKeyId) {
        throw new Error('Remote public key identik dengan key lokal; pertukaran ECDH ditolak.');
    }

    const remotePublicKey = await importRemotePublicKeyJwk(cleanRemoteJwk);
    const sharedSecret = await deriveEcdhSharedSecretBits(localKeyPair.privateKey, remotePublicKey);

    return {
        sharedSecret,
        remoteKeyId,
    };
}

/**
 * Build deterministic bilateral ECDH session context independent of initiator/responder order.
 * @param {string} roomId
 * @param {string} localUserId
 * @param {string} remoteUserId
 * @returns {{roomId: string, participants: [string, string], contextLabel: string}}
 */
export function buildEcdhSessionContext(roomId, localUserId, remoteUserId) {
    if (!roomId || !localUserId || !remoteUserId) {
        throw new Error('roomId, localUserId, dan remoteUserId wajib diisi untuk konteks ECDH.');
    }
    const participants = [String(localUserId), String(remoteUserId)].sort();
    return {
        roomId: String(roomId),
        participants,
        contextLabel: `SecureLink-ECDH-v1:${roomId}:${participants[0]}:${participants[1]}`,
    };
}

/**
 * Derive a 32-byte deterministic HKDF salt bound to room and optional key-exchange salt.
 * @param {string} roomId
 * @param {string} [extraSalt='']
 * @returns {Promise<Uint8Array>}
 */
export async function buildHkdfSalt(roomId, extraSalt = '') {
    if (!roomId) {
        throw new Error('roomId wajib diisi untuk derivasi salt HKDF.');
    }
    const seed = `SecureLink-HKDF-Salt-v1:${roomId}:${extraSalt}`;
    const digest = await getSubtleCrypto().digest('SHA-256', new TextEncoder().encode(seed));
    return new Uint8Array(digest);
}

/**
 * Derive a non-extractable 256-bit AES-GCM CryptoKey from an ECDH shared secret using HKDF-SHA-256.
 * @param {Uint8Array} sharedSecret
 * @param {Object} options
 * @param {string} options.roomId
 * @param {string} options.localUserId
 * @param {string} options.remoteUserId
 * @param {string} [options.extraSalt='']
 * @returns {Promise<CryptoKey>}
 */
export async function deriveAesGcmKeyWithHkdf(sharedSecret, options) {
    if (!(sharedSecret instanceof Uint8Array) || sharedSecret.length < 32) {
        throw new Error('Shared secret HKDF harus berupa Uint8Array minimal 32 byte.');
    }

    const subtle = getSubtleCrypto();
    const { contextLabel } = buildEcdhSessionContext(
        options?.roomId,
        options?.localUserId,
        options?.remoteUserId
    );
    const salt = await buildHkdfSalt(options.roomId, options?.extraSalt || '');
    const info = new TextEncoder().encode(`${contextLabel}:AES-GCM-256`);

    // Copy before zeroing caller buffer after import
    const secretCopy = new Uint8Array(sharedSecret);
    try {
        const hkdfBaseKey = await subtle.importKey(
            'raw',
            secretCopy,
            'HKDF',
            false,
            ['deriveKey']
        );

        return await subtle.deriveKey(
            {
                name: 'HKDF',
                hash: 'SHA-256',
                salt,
                info,
            },
            hkdfBaseKey,
            {
                name: 'AES-GCM',
                length: 256,
            },
            false,
            ['encrypt', 'decrypt']
        );
    } finally {
        secretCopy.fill(0);
    }
}

/**
 * Complete ECDH + HKDF pipeline to establish a non-extractable AES-GCM-256 peer session key.
 * @param {CryptoKeyPair} localKeyPair
 * @param {JsonWebKey} remoteJwk
 * @param {Object} context
 * @param {string} context.roomId
 * @param {string} context.localUserId
 * @param {string} context.remoteUserId
 * @param {string} [context.extraSalt='']
 * @returns {Promise<{aesKey: CryptoKey, remoteKeyId: string, algorithm: string}>}
 */
export async function derivePeerEncryptionKey(localKeyPair, remoteJwk, context) {
    const { sharedSecret, remoteKeyId } = await deriveSharedSecretFromJwk(localKeyPair, remoteJwk);
    try {
        const aesKey = await deriveAesGcmKeyWithHkdf(sharedSecret, context);
        return {
            aesKey,
            remoteKeyId,
            algorithm: 'ECDH-P256+HKDF-SHA256+AES-GCM-256',
        };
    } finally {
        sharedSecret.fill(0);
    }
}

/**
 * Validate raw IV bytes for AES-GCM (must be exact 12-byte / 96-bit Uint8Array).
 * @param {Uint8Array} ivBytes
 * @returns {Uint8Array}
 */
export function validateIvBytes(ivBytes) {
    if (!(ivBytes instanceof Uint8Array) || ivBytes.length !== AES_GCM_IV_LENGTH) {
        throw new Error(`IV/Nonce AES-GCM harus berukuran tepat ${AES_GCM_IV_LENGTH} byte (96-bit).`);
    }
    return ivBytes;
}

/**
 * Decode Base64 IV and validate its 96-bit length.
 * @param {string} ivBase64
 * @returns {Uint8Array}
 */
export function decodeAndValidateIv(ivBase64) {
    const bytes = base64ToBytes(ivBase64);
    return validateIvBytes(bytes);
}

/**
 * Manages unique 96-bit Nonce/IV generation and replay detection per key epoch.
 * Layout: 4-byte CSPRNG prefix + 8-byte big-endian monotonic counter.
 */
export class NonceManager {
    constructor(maxIncomingHistory = MAX_NONCE_HISTORY) {
        this.maxIncomingHistory = maxIncomingHistory;
        this.reset();
    }

    /**
     * Reset prefix, counter, and replay tracking (call when session key rotates).
     */
    reset() {
        this.prefix = getWebCrypto().getRandomValues(new Uint8Array(4));
        this.counter = 0n;
        this.usedOutgoingNonces = new Set();
        this.seenIncomingNonces = new Set();
    }

    /**
     * Generate a guaranteed-unique 12-byte (96-bit) IV for encryption.
     * @returns {Uint8Array}
     */
    nextIv() {
        if (this.counter >= 0xFFFFFFFFFFFFFFFFn) {
            throw new Error('Batas counter nonce tercapai; rotasi kunci E2EE diperlukan.');
        }

        this.counter += 1n;
        const iv = new Uint8Array(AES_GCM_IV_LENGTH);
        iv.set(this.prefix, 0);

        const view = new DataView(iv.buffer, iv.byteOffset, iv.byteLength);
        view.setBigUint64(4, this.counter, false);

        const key = bytesToBase64(iv);
        if (this.usedOutgoingNonces.has(key)) {
            throw new Error('Deteksi duplikasi Nonce/IV keluar; operasi enkripsi dibatalkan.');
        }

        this.usedOutgoingNonces.add(key);
        if (this.usedOutgoingNonces.size > this.maxIncomingHistory) {
            const oldest = this.usedOutgoingNonces.values().next().value;
            this.usedOutgoingNonces.delete(oldest);
        }

        return iv;
    }

    /**
     * Validate and record an incoming IV to prevent nonce replay attacks.
     * @param {Uint8Array|string} ivOrBase64
     * @returns {Uint8Array}
     */
    registerIncomingIv(ivOrBase64) {
        const ivBytes = typeof ivOrBase64 === 'string'
            ? decodeAndValidateIv(ivOrBase64)
            : validateIvBytes(ivOrBase64);

        const key = bytesToBase64(ivBytes);
        if (this.seenIncomingNonces.has(key)) {
            throw new Error('Nonce/IV replay terdeteksi untuk kunci sesi saat ini.');
        }

        this.seenIncomingNonces.add(key);
        if (this.seenIncomingNonces.size > this.maxIncomingHistory) {
            const oldest = this.seenIncomingNonces.values().next().value;
            this.seenIncomingNonces.delete(oldest);
        }

        return ivBytes;
    }

    getStats() {
        return {
            ivLengthBytes: AES_GCM_IV_LENGTH,
            outgoingCount: this.counter.toString(),
            trackedIncomingCount: this.seenIncomingNonces.size,
        };
    }
}

/**
 * Build canonical Additional Authenticated Data (AAD) binding room, sender, and message ID.
 * @param {Object|null} aadContext
 * @returns {Uint8Array}
 */
export function buildMessageAad(aadContext) {
    if (!aadContext || typeof aadContext !== 'object') {
        return new Uint8Array(0);
    }

    const canonical = JSON.stringify({
        roomId: aadContext.roomId ?? '',
        senderId: aadContext.senderId ?? '',
        messageId: aadContext.messageId ?? '',
    });
    return new TextEncoder().encode(canonical);
}

/**
 * Encrypt plain-text message with AES-GCM (256-bit) and 128-bit authentication tag.
 * @param {string} plaintext
 * @param {CryptoKey} aesKey
 * @param {NonceManager} nonceManager
 * @param {Object|null} [aadContext=null]
 * @returns {Promise<{ciphertext: string, iv: string, algorithm: string, tagLength: number, encrypted: boolean}>}
 */
export async function encryptAesGcm(plaintext, aesKey, nonceManager, aadContext = null) {
    if (typeof plaintext !== 'string') {
        throw new Error('Plaintext pesan harus berupa string.');
    }
    if (!aesKey || aesKey.algorithm?.name !== 'AES-GCM') {
        throw new Error('Kunci enkripsi AES-GCM tidak tersedia atau tidak valid.');
    }
    if (!(nonceManager instanceof NonceManager)) {
        throw new Error('NonceManager wajib disediakan untuk enkripsi AES-GCM.');
    }

    const iv = nonceManager.nextIv();
    const encoded = new TextEncoder().encode(plaintext);
    const additionalData = buildMessageAad(aadContext);

    const encryptedBuffer = await getSubtleCrypto().encrypt(
        {
            name: 'AES-GCM',
            iv,
            additionalData,
            tagLength: AES_GCM_TAG_LENGTH,
        },
        aesKey,
        encoded
    );

    return {
        ciphertext: bytesToBase64(new Uint8Array(encryptedBuffer)),
        iv: bytesToBase64(iv),
        algorithm: 'AES-GCM-256',
        tagLength: AES_GCM_TAG_LENGTH,
        encrypted: true,
    };
}

/**
 * Decrypt and authenticate AES-GCM payload. Rejects tampered, replayed, or unauthenticated data.
 * @param {{ciphertext: string, iv: string, encrypted?: boolean}} payload
 * @param {CryptoKey} aesKey
 * @param {NonceManager|null} [nonceManager=null]
 * @param {Object|null} [aadContext=null]
 * @returns {Promise<string>}
 */
export async function decryptAesGcm(payload, aesKey, nonceManager = null, aadContext = null) {
    if (!payload || typeof payload.ciphertext !== 'string' || typeof payload.iv !== 'string') {
        throw new Error('Payload terenkripsi tidak lengkap (ciphertext atau IV hilang).');
    }
    if (payload.encrypted === false) {
        throw new Error('Payload tidak terenkripsi ditolak oleh kebijakan E2EE.');
    }
    if (!aesKey || aesKey.algorithm?.name !== 'AES-GCM') {
        throw new Error('Kunci dekripsi AES-GCM tidak tersedia atau tidak valid.');
    }

    const iv = nonceManager instanceof NonceManager
        ? nonceManager.registerIncomingIv(payload.iv)
        : decodeAndValidateIv(payload.iv);

    const ciphertextBytes = base64ToBytes(payload.ciphertext);
    const additionalData = buildMessageAad(aadContext);

    try {
        const decryptedBuffer = await getSubtleCrypto().decrypt(
            {
                name: 'AES-GCM',
                iv,
                additionalData,
                tagLength: AES_GCM_TAG_LENGTH,
            },
            aesKey,
            ciphertextBytes
        );

        return new TextDecoder().decode(decryptedBuffer);
    } catch (err) {
        throw new Error('Autentikasi atau dekripsi AES-GCM gagal: integritas ciphertext atau tag tidak valid.');
    }
}




