import React from "react";

import "./VideoPlayerIframe.css";
import {LinearProgress} from "@material-ui/core";

export default class VideoPlayerIframe extends React.Component {

    state = {
        load: false
    };

    componentDidUpdate(prevProps, prevState, snapshot) {
        if(prevProps.url !== this.props.url) {
            this.setState({load: false})
        }
    }

    render() {
        return <>
            {!this.state.load
                ? <LinearProgress style={{maxWidth: '950px', margin: 'auto'}} />
                : null
            }
            <iframe
                onLoad          = {() => this.setState({load: true})}
                className       = "video-player__iframe"
                title           = {this.props.url}
                allowFullScreen = {true}
                frameBorder     = "false"
                src             = {this.props.url}
            />
        </>
    }
}