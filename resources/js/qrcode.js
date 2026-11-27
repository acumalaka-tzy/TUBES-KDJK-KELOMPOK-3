import QRCode from 'qrcode';
import jsQR from 'jsqr';

/**
 * Render a real scannable QR Code onto an HTML5 Canvas element.
 */
export async function renderQrToCanvas(canvas, text, options = {}) {
    if (!canvas || !text) return false;
    const size = options.size || 200;

    try {
        await QRCode.toCanvas(canvas, text, {
            width: size,
            margin: 2,
            errorCorrectionLevel: 'M',
            color: {
                dark: options.colorDark || '#20292B',
                light: options.colorLight || '#FFFFFF',
            },
        });
        return true;
    } catch (err) {
        console.error('[SecureLink QR] Failed to render QR code:', err);
        return false;
    }
}

/**
 * Download rendered QR Canvas as a PNG image.
 */
export function downloadQrPng(canvas, filename = 'securelink-invite-qr.png') {
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    document.body.appendChild(link);
    link.click();
    link.remove();
}

/**
 * Parse a scanned QR payload or pasted invitation link into { roomId, inviteToken }.
 */
export function parseInvitationInput(rawText) {
    const trimmed = (rawText || '').trim();
    if (!trimmed) return { roomId: null, inviteToken: null };

    try {
        const url = new URL(trimmed, window.location.origin);
        const roomId = url.searchParams.get('room_id');
        const inviteToken = url.searchParams.get('invite_token');
        if (roomId) {
            return { roomId: roomId.trim().toUpperCase(), inviteToken: inviteToken ? inviteToken.trim() : null };
        }
    } catch (e) {
        // Not a URL, fall through to plain Room ID check
    }

    const upper = trimmed.toUpperCase();
    if (/^[A-Z0-9-]{4,32}$/.test(upper)) {
        return { roomId: upper, inviteToken: null };
    }

    return { roomId: null, inviteToken: null };
}

let activeStream = null;
let scannerRafId = null;

/**
 * Start live camera QR scanner using navigator.mediaDevices + jsQR.
 */
export async function startQrScanner({ videoEl, canvasEl, onDetected, onError }) {
    stopQrScanner();

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        onError?.('unsupported', 'Browser tidak mendukung akses kamera. Gunakan input manual atau unggah gambar QR.');
        return;
    }

    try {
        activeStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false,
        });

        videoEl.srcObject = activeStream;
        videoEl.setAttribute('playsinline', 'true');
        await videoEl.play();

        const ctx = canvasEl.getContext('2d', { willReadFrequently: true });

        const tick = () => {
            if (!activeStream) return;
            if (videoEl.readyState === videoEl.HAVE_ENOUGH_DATA) {
                canvasEl.width = videoEl.videoWidth;
                canvasEl.height = videoEl.videoHeight;
                ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                const imageData = ctx.getImageData(0, 0, canvasEl.width, canvasEl.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code && code.data) {
                    stopQrScanner();
                    onDetected?.(code.data);
                    return;
                }
            }
            scannerRafId = requestAnimationFrame(tick);
        };

        scannerRafId = requestAnimationFrame(tick);
    } catch (err) {
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            onError?.('permission_denied', 'Izin kamera ditolak. Aktifkan izin kamera atau masukkan tautan undangan secara manual.');
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            onError?.('no_camera', 'Kamera tidak ditemukan pada perangkat ini. Gunakan input manual atau unggah gambar QR.');
        } else {
            onError?.('camera_error', 'Gagal mengakses kamera: ' + (err.message || 'Kesalahan perangkat.'));
        }
    }
}

/**
 * Stop active camera stream and animation loop.
 */
export function stopQrScanner() {
    if (scannerRafId) {
        cancelAnimationFrame(scannerRafId);
        scannerRafId = null;
    }
    if (activeStream) {
        activeStream.getTracks().forEach((track) => track.stop());
        activeStream = null;
    }
}

/**
 * Decode a QR code from an uploaded image File.
 */
export function scanQrFromFile(file) {
    return new Promise((resolve, reject) => {
        if (!file) {
            reject(new Error('File gambar tidak ditemukan.'));
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                if (code && code.data) {
                    resolve(code.data);
                } else {
                    reject(new Error('QR Code tidak terbaca pada gambar tersebut.'));
                }
            };
            img.onerror = () => reject(new Error('Gagal memuat gambar QR Code.'));
            img.src = event.target.result;
        };
        reader.onerror = () => reject(new Error('Gagal membaca file gambar.'));
        reader.readAsDataURL(file);
    });
}

window.renderQrToCanvas = renderQrToCanvas;
window.downloadQrPng = downloadQrPng;
window.parseInvitationInput = parseInvitationInput;
window.startQrScanner = startQrScanner;
window.stopQrScanner = stopQrScanner;
window.scanQrFromFile = scanQrFromFile;
