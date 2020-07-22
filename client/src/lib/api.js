import * as xhr from "xhr";
import * as buildQuery from "http-build-query";

// eslint-disable-next-line no-process-env,max-len
export const API_PATH = process.env.REACT_APP_API_BASE_PATH || "http://todonime.lc/api/";

// eslint-disable-next-line func-style,max-lines-per-function
export function fetch (url, params, method) {

    // eslint-disable-next-line max-lines-per-function
    return new Promise((resolve, reject) => {

        const options = {
            "headers": {},
            "withCredentials": true,
            "method": method || "GET",
            "url": API_PATH + url
        };

        if ([
            "POST",
            "PUT",
            "DELETE"
            // eslint-disable-next-line no-magic-numbers
        ].indexOf(method || "GET") > -1) {

            // eslint-disable-next-line max-len
            options.headers["Content-Type"] = "application/x-www-form-urlencoded";
            options.body = buildQuery(params);

        } else {
            options.url = `${API_PATH + url}?${buildQuery(params)}`;
        }

        xhr(
            options,
            (err, res) => {

                // eslint-disable-next-line no-eq-null
                if (res.body == null) {

                    throw new Error("protocol_error");

                }

                if (JSON.parse(res.body).error) {

                    reject(JSON.parse(res.body));

                } else {

                    resolve(JSON.parse(res.body));

                }

            }
        );

    });

}
