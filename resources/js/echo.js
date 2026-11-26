import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const reverbHost = import.meta.env.VITE_REVERB_HOST && import.meta.env.VITE_REVERB_HOST !== '127.0.0.1'
    ? import.meta.env.VITE_REVERB_HOST
    : window.location.hostname || '127.0.0.1';
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

window.EchoConnectionState = 'initializing';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'securelink_key',
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    },
});

const pusherConnection = window.Echo?.connector?.pusher?.connection;

if (pusherConnection) {
    const emitWsState = (state, extra = {}) => {
        window.EchoConnectionState = state;
        window.dispatchEvent(new CustomEvent('securelink:ws-state', {
            detail: { state, ...extra },
        }));
    };

    pusherConnection.bind('state_change', ({ previous, current }) => {
        emitWsState(current, { previous });
    });

    pusherConnection.bind('connected', () => {
        emitWsState('connected');
    });

    pusherConnection.bind('disconnected', () => {
        emitWsState('disconnected');
    });

    pusherConnection.bind('unavailable', () => {
        emitWsState('unavailable');
    });

    pusherConnection.bind('error', (err) => {
        emitWsState('error', { error: err });
    });
}
