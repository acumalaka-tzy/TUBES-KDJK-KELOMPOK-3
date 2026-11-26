/**
 * SecureLink DTLS Transport Verification Module
 * Implements PRD Section 3.7, 12.1, 14.18, and Appendix B.
 *
 * Strictly separates DTLS transport verification from application-layer E2EE.
 */

export const VALID_DTLS_STATES = ['new', 'connecting', 'connected', 'closed', 'failed', 'unverified'];

/**
 * Inspect SDP string for DTLS fingerprint and setup attributes.
 * Note: SDP presence indicates DTLS negotiation capability, NOT active connection state.
 * @param {string} sdp
 * @returns {{hasFingerprint: boolean, algorithm: string|null, setup: string|null}}
 */
export function inspectSdpDtlsFingerprint(sdp) {
    if (typeof sdp !== 'string' || sdp.trim() === '') {
        return { hasFingerprint: false, algorithm: null, setup: null };
    }

    const fpMatch = sdp.match(/^a=fingerprint:(\S+)\s+([0-9A-Fa-f:]+)/m);
    const setupMatch = sdp.match(/^a=setup:(actpass|active|passive)/m);

    return {
        hasFingerprint: Boolean(fpMatch),
        algorithm: fpMatch ? fpMatch[1].toLowerCase() : null,
        setup: setupMatch ? setupMatch[1].toLowerCase() : null,
    };
}

/**
 * Verify DTLS transport state and connection path (direct vs relay) from RTCPeerConnection.
 * @param {RTCPeerConnection} pc
 * @returns {Promise<{
 *   dtlsState: string,
 *   dtlsVerified: boolean,
 *   dtlsCipher: string|null,
 *   srtpCipher: string|null,
 *   tlsVersion: string|null,
 *   connectionType: 'direct'|'relay'|'unknown',
 *   iceState: string,
 *   peerConnectionState: string
 * }>}
 */
export async function verifyPeerDtlsTransport(pc) {
    if (!pc) {
        return {
            dtlsState: 'unverified',
            dtlsVerified: false,
            dtlsCipher: null,
            srtpCipher: null,
            tlsVersion: null,
            connectionType: 'unknown',
            iceState: 'new',
            peerConnectionState: 'new',
        };
    }

    let dtlsState = pc.sctp?.transport?.state || 'unverified';
    let dtlsCipher = null;
    let srtpCipher = null;
    let tlsVersion = null;
    let connectionType = 'unknown';

    if (typeof pc.getStats === 'function') {
        try {
            const stats = await pc.getStats();
            let selectedPairId = null;

            stats.forEach((report) => {
                if (report.type === 'transport') {
                    if (report.dtlsState) {
                        dtlsState = report.dtlsState;
                    }
                    dtlsCipher = report.dtlsCipher || dtlsCipher;
                    srtpCipher = report.srtpCipher || srtpCipher;
                    tlsVersion = report.tlsVersion || tlsVersion;
                    selectedPairId = report.selectedCandidatePairId || selectedPairId;
                }
            });

            stats.forEach((report, reportKey) => {
                if (report.type !== 'candidate-pair') return;
                const reportId = report.id || reportKey;
                const isSelected = selectedPairId
                    ? reportId === selectedPairId
                    : report.selected === true || report.state === 'succeeded';

                if (isSelected) {
                    const localCandidate = stats.get(report.localCandidateId);
                    const remoteCandidate = stats.get(report.remoteCandidateId);
                    if (
                        localCandidate?.candidateType === 'relay' ||
                        remoteCandidate?.candidateType === 'relay'
                    ) {
                        connectionType = 'relay';
                    } else if (localCandidate || remoteCandidate) {
                        connectionType = 'direct';
                    }
                }
            });
        } catch (err) {
            // Leave unverified/fallback state when stats are unavailable
        }
    }

    if (!VALID_DTLS_STATES.includes(dtlsState)) {
        dtlsState = 'unverified';
    }

    return {
        dtlsState,
        dtlsVerified: dtlsState === 'connected',
        dtlsCipher,
        srtpCipher,
        tlsVersion,
        connectionType,
        iceState: pc.iceConnectionState || 'new',
        peerConnectionState: pc.connectionState || 'new',
    };
}

/**
 * Evaluate overall security posture honestly per PRD Section 3.7 & Appendix B.
 * DTLS active does NOT automatically mean E2EE is active.
 * @param {Object} input
 * @param {string} input.dtlsState
 * @param {string} input.e2eeStatus
 * @param {boolean} input.e2eeEnabled
 * @param {string} input.dataChannelState
 * @returns {{
 *   dtlsConnected: boolean,
 *   e2eeActive: boolean,
 *   dataChannelOpen: boolean,
 *   secureConnection: boolean,
 *   summaryStatus: 'secure'|'connecting'|'reconnecting'|'unverified'|'disconnected'
 * }}
 */
export function evaluateTransportAndE2eeSecurity({
    dtlsState = 'unverified',
    e2eeStatus = 'uninitialized',
    e2eeEnabled = true,
    dataChannelState = 'closed',
}) {
    const dtlsConnected = dtlsState === 'connected';
    const e2eeActive = e2eeEnabled ? e2eeStatus === 'active' : false;
    const dataChannelOpen = dataChannelState === 'open';
    const secureConnection = dataChannelOpen && dtlsConnected && (!e2eeEnabled || e2eeActive);

    let summaryStatus = 'disconnected';
    if (secureConnection) {
        summaryStatus = 'secure';
    } else if (dtlsState === 'connecting' || e2eeStatus === 'exchanging' || dataChannelState === 'connecting') {
        summaryStatus = 'connecting';
    } else if (dataChannelOpen && (!dtlsConnected || (e2eeEnabled && !e2eeActive))) {
        summaryStatus = 'unverified';
    }

    return {
        dtlsConnected,
        e2eeActive,
        dataChannelOpen,
        secureConnection,
        summaryStatus,
    };
}
