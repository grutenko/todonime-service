import React from "react";

import "./TodonimePlayerIframe.css";
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
                ? <LinearProgress style={{maxWidth: '70vw', margin: 'auto'}} className="block"/>
                : null
            }
            <iframe
                style           = {{marginTop: !this.state.load ? 0 : '4px'}}
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