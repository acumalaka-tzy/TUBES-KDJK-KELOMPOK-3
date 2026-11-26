/**
 * SecureLink End-to-End Encryption (E2EE) Cryptography Module
 * Implements PRD Section 4.1 (Key Pair, ECDH, HKDF, AES-GCM, Nonce/IV Lifecycle).
 */

export const E2EE_CURVE = 'P-256';

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
