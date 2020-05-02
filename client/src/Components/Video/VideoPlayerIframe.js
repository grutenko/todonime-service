import React from "react";

import "./VideoPlayerIframe.css";

export default function VideoPlayerIframe ({url}) {

    return <iframe
        className="video-player__iframe"
        title={url}
        allowFullScreen={true}
        frameBorder="false"
        src={url}
    />;

}
