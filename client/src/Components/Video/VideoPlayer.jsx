import React from "react";

import Loader from "../Misc/Loader";

import {fetch as __fetch} from "../../lib/api";
import VideoPlayerIframe from "./VideoPlayerIframe";
import {IconButton, withStyles} from "@material-ui/core";
import ViewListIcon from "@material-ui/icons/ViewList";
import KeyboardArrowLeftIcon from "@material-ui/icons/KeyboardArrowLeft";
import KeyboardArrowRightIcon from "@material-ui/icons/KeyboardArrowRight";
import CheckIcon from "@material-ui/icons/Check";
import ArtTrackIcon from "@material-ui/icons/ArtTrack";
import {drawerWidth} from "../Menu";
import clsx from "clsx";
import VideosList from "./VideosList";
import AnimeInfo from "../Anime/AnimeInfo";

class VideoPlayer extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            "data": null,
            "loaded": false,
            "rightMenuPortal": false
        };

    }

    componentDidMount () {

        this.fetch();

    }

    componentDidUpdate (prevProps, prevState, snapshot) {

        if (this.props.match.params.id !== prevProps.match.params.id) {

            this.fetch();
            if (this.props.menuOpen) {

                this.onOpenTranslationsList();

            }

        }

    }

    fetch () {

        this.setState({"loaded": false});

        __fetch(`video/${this.props.match.params.id}`)
            .then((result) => this.setState({"loaded": true,
                // eslint-disable-next-line sort-keys
                "data": result.data}));

    }

    onOpenTranslationsList () {

        this.props.setMenu(<VideosList
            currentId={this.props.match.params.id}
            videos={this.state.data.videos}
        />);

    }

    onOpenAnimeInfo () {

        this.props.setMenu(<AnimeInfo anime={this.state.data.anime} />);

    }

    // eslint-disable-next-line class-methods-use-this
    render () {

        const {loaded, data} = this.state,
            {menuOpen} = this.props;

        // eslint-disable-next-line no-ternary
        return <>
            {loaded
                ? <VideoPlayerIframe url={data.url}/>
                : <Loader/>}
            <div
                className={clsx(
                    this.props.classes.fixedToolBox,
                    {
                        [this.props.classes.fixedToolBoxShift]: menuOpen
                    }
                )}
            >
                <IconButton
                    onClick={this.onOpenTranslationsList.bind(this)}
                >
                    <ViewListIcon />
                </IconButton><br/>
                <IconButton
                    onClick={this.onOpenAnimeInfo.bind(this)}
                >
                    <ArtTrackIcon />
                </IconButton><br/>
                <IconButton>
                    <KeyboardArrowLeftIcon />
                </IconButton><br/>
                <IconButton>
                    <CheckIcon color="primary" />
                </IconButton><br/>
                <IconButton>
                    <KeyboardArrowRightIcon />
                </IconButton>
            </div>
        </>;

    }

}

const styles = (theme) => ({
    "fixedToolBox": {
        "background": "white",
        "margin": "11px",
        "position": "fixed",
        "zIndex": 2500,
        "right": 0,
        "top": 50
    },
    "fixedToolBoxShift": {
        "marginRight": drawerWidth + 11,
        "transition": theme.transitions.create(
            [
                "margin",
                "width"
            ],
            {
                "duration": theme.transitions.duration.enteringScreen,
                "easing": theme.transitions.easing.easeOut
            }
        )
    }
});

export default withStyles(styles)(VideoPlayer);


