import * as buildQuery from "http-build-query";

/**
 * @param channel
 * @param filter
 */
export function createWs(channel, filter) {
    return new WebSocket(
        "ws://ws.todonime.lc/?" + buildQuery({channel, filter})
    );
}