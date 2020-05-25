import * as buildQuery from "http-build-query";

/**
 * @param channel
 * @param filter
 */
export function createWs(channel, filter) {
    return new WebSocket(
        (process.env.REACT_APP_WS_BASE_PATH || "wss://ws.todonime.ru") + buildQuery({channel, filter})
    );
}