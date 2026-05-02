import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/** @type {import('laravel-echo').default | null} */
let echoInstance = null;

let axiosInterceptorInstalled = false;

/**
 * Crea la instancia de Echo solo cuando una vista lo necesita (menos conexiones irrelevantes).
 * Idempotente.
 *
 * @returns {import('laravel-echo').default | null}
 */
export function ensureEcho() {
    const key = import.meta.env.VITE_PUSHER_APP_KEY;
    if (typeof key !== 'string' || key.length === 0) {
        return null;
    }

    if (echoInstance) {
        return echoInstance;
    }

    const scheme = import.meta.env.VITE_PUSHER_SCHEME ?? 'https';
    const forceTLS = scheme === 'https';
    const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1';
    const wsHost = import.meta.env.VITE_PUSHER_HOST;
    const wsPort = Number(import.meta.env.VITE_PUSHER_PORT ?? 6001);

    const echoOptions = {
        broadcaster: 'pusher',
        key,
        cluster,
        forceTLS,
        encrypted: forceTLS,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    };

    if (typeof wsHost === 'string' && wsHost.length > 0) {
        echoOptions.wsHost = wsHost;
        echoOptions.wsPort = wsPort;
        echoOptions.wssPort = wsPort;
    }

    echoInstance = new Echo(echoOptions);
    window.Echo = echoInstance;

    bindEchoConnectionState(echoInstance);

    return echoInstance;
}

/**
 * @param {import('axios').AxiosStatic} axios
 */
export function installEchoAxiosInterceptor(axios) {
    if (axiosInterceptorInstalled) {
        return;
    }
    axiosInterceptorInstalled = true;

    axios.interceptors.request.use((config) => {
        const echo = echoInstance ?? window.Echo;
        if (echo && typeof echo.socketId === 'function') {
            const id = echo.socketId();
            if (id) {
                config.headers['X-Socket-Id'] = id;
            }
        }

        return config;
    });
}

/**
 * @param {import('laravel-echo').default} echo
 */
function bindEchoConnectionState(echo) {
    const connector = echo.connector;
    if (!connector || typeof connector.onConnectionChange !== 'function') {
        return;
    }

    const emit = (online) => {
        window.__echoWsOnline = online;
        window.dispatchEvent(
            new CustomEvent('entre-sabores:echo-connection', {
                detail: { online },
            }),
        );
    };

    emit(typeof connector.connectionStatus === 'function' && connector.connectionStatus() === 'connected');

    connector.onConnectionChange((status) => {
        emit(status === 'connected');
    });
}
