import React from "react";

import Loader from "../Misc/Loader";

import moment from "moment";
import "moment/locale/ru";
import {fetch} from "../../lib/api";
import VideoPlayerIframe from "./VideoPlayerIframe";
import {Button} from "@material-ui/core";
import ViewListIcon from "@material-ui/icons/ViewList";
import TheatersIcon from "@material-ui/icons/Theaters";
import VideosList from "./VideosList";
import AnimeInfo from "../Anime/AnimeInfo";
import {alreadyShowed, setShow, unsetShow} from "../../lib/promt";
import Snackbar from "@material-ui/core/Snackbar";
import MuiAlert from "@material-ui/lab/Alert";
import Comments from "./Comments";
import CheckIcon from '@material-ui/icons/Check';

import "./VideoPlayer.css";
import AnimeCard from "../Anime/AnimeCard";

moment.locale("ru");

export default class VideoPlayer extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            "showLogin": alreadyShowed("login"),
            "showLogout": alreadyShowed("logout"),
            "data": null,
            "loaded": false,
            "rightMenuPortal": false
        };

        if (this.state.showLogin) {

            unsetShow("login");

        }
        if (this.state.showLogout) {

            unsetShow("logout");

        }

    }

    componentDidMount () {

        this.fetch();

    }

    componentDidUpdate (prevProps, prevState, snapshot) {

        if (this.props.match.params.id !== prevProps.match.params.id) {

            this.fetch();

        }

    }

    fetch () {

        this.setState({"loaded": false});

        fetch(`video/${this.props.match.params.id}`)
            .then((result) => {
                this.setState({ "loaded": true, "data": result.data });
            });

    }

    render () {

        const {
            loaded,
            data,
            showLogin,
            showLogout
        } = this.state;

        if (loaded && data.user === undefined && !alreadyShowed("auth")) {

            setShow("auth");
            window.location.href = `https://auth.todonime.ru/?back_url${window.location}`;

        }

        return loaded
            ? <>
                <AuthSnackbar
                    show    = { showLogin }
                    onClose = { () => this.setState({"showLogin": false}) }
                >
                    Вы успешно авторизировались через shikimori.one
                </AuthSnackbar>
                <AuthSnackbar
                    show    = { showLogout }
                    onClose = { () => this.setState({"showLogout": false}) }
                >
                    Вы успешно вышли из аккаунта
                </AuthSnackbar>
                <VideoPlayerIframe url={data.url}/>
                <Toolbar
                    canComplete = { Boolean(data.user) }
                    isWatched   = { data.is_watched }
                    history     = { this.props.history }
                    nextEpisode = { data.next_episode.video_id}
                    data        = { data }
                    setMenu     = { this.props.setMenu }
                />
                <Comments
                    animeId     = { data.anime._id.$oid }
                    episode     = { data.episode }
                /></>
            : <Loader/>;
    }

}

class Toolbar extends React.Component {

    /**
     * @type {{
     *      buttons: {margin: string, maxWidth: string},
     *      root: {padding: string, background: string, marginTop: string}
     * }}
     */
    styles = {
        root: {
            marginTop: "15px",
            padding: "15px",
            background: "white"
        },
        buttons: {
            maxWidth: "800px",
            margin: "auto"
        },
        animeInfo: {
            maxWidth: "800px",
            margin: "auto",
            marginTop: "15px"
        },
        animeData: {
            marginLeft: '5px'
        }
    }

    /**
     * @type {{
     *      completing: boolean
     * }}
     */
    state = {
        completing: false
    }

    onOpenList() {
        const {
            setMenu,
            data
        } = this.props;

        setMenu(<VideosList
            setMenu     = { setMenu }
            currentId   = { data._id.$oid }
            currentKind = { data.kind }
            videos      = { data.videos }
        />);
    }

    onOpenAnimeInfo() {
        const {
            setMenu,
            data
        } = this.props;

        setMenu(<AnimeInfo
            anime           = {data.anime}
            currentEpisode  = {data.episode}
            lastEpisode     = {data.last_watched_episode}
        />);
    }

    bumpEpisode() {
        const {
            data: {
                anime: {
                    _id
                },
                episode,
            },
            history,
            canComplete,
            isWatched,
            nextEpisode
        } = this.props

        if(!canComplete || isWatched || this.state.completing) {
            return;
        }

        this.setState({completing: true});

        fetch(
            "user/episode/watched",
            {
                "anime_id" : _id.$oid,
                "episode"  : episode
            },
            "PUT"
        ).then(() => {
            this.setState({completing: false});
            history.push(`/v/${nextEpisode}`);
        })
    }

    renderButtons() {
        const {
            isWatched
        } = this.props;

        return <div style={this.styles.buttons}>
            <Button
                onClick     = {this.onOpenList.bind(this)}
                startIcon   = {<TheatersIcon/>}
            >
                <span className="hide-630px">Переводы</span>
            </Button>
            <Button
                onClick     = {this.onOpenAnimeInfo.bind(this)}
                startIcon   = {<ViewListIcon/>}
            >
                <span className="hide-630px">Эпизоды</span>
            </Button>
            <Button
                variant     = {isWatched ? "text" : "contained"}
                color       = {isWatched ? "primary" : "secondary"}
                onClick     = {this.bumpEpisode.bind(this)}
                startIcon   = {<CheckIcon/>}
                style       = {{float: "right"}}
            >
                <span className="hide-630px">
                    {isWatched ? "Просмотрена" : "Отметить просмотренной"}
                </span>
            </Button>
        </div>
    }

    renderAnimeInfo() {
        const {
            data
        } = this.props;

        return <div style={this.styles.animeInfo}>
                <AnimeCard
                anime = {data.anime}
                currentEpisode  = {data.episode}
                lastEpisode     = {data.last_watched_episode}
            />
        </div>
    }

    render() {
        return <div style={this.styles.root}>
            { this.renderButtons() }
            { this.renderAnimeInfo() }
        </div>
    }
}

/**
 * @param show
 * @param children
 * @param onClose
 * @returns {*}
 * @constructor
 */
function AuthSnackbar({show, children, onClose}) {
    return <Snackbar
        open             = { show }
        autoHideDuration = { 6000 }
        onClose          = { onClose }
        anchorOrigin={{
            "vertical"   : "top",
            "horizontal" : "center"
        }}
        key="top,right"
    >
        <Alert severity="info">
            { children }
        </Alert>
    </Snackbar>
}

/**
 * @param props
 * @returns {*}
 * @constructor
 */
function Alert (props) {

    return <MuiAlert elevation={6} variant="filled" {...props} />;

}