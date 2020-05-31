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
import Dialog from "@material-ui/core/Dialog";
import DialogTitle from "@material-ui/core/DialogTitle";
import DialogContent from "@material-ui/core/DialogContent";
import DialogContentText from "@material-ui/core/DialogContentText";
import DialogActions from "@material-ui/core/DialogActions";

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

    fetch (useLoader) {

        if( useLoader === true || useLoader === undefined) {
            this.setState({"loaded": false});
        }

        fetch(`video/${this.props.match.params.id}`)
            .then((result) => {
                this.setState({ "loaded": true, "data": result.data });
            });

    }

    fetchWithoutLoader() {
        this.fetch(false);
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

        return <>
            {data !== null
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
                        nextEpisode = { data.next_episode ? data.next_episode.video_id : null}
                        data        = { data }
                        onUpdate    = { this.fetchWithoutLoader.bind(this) }
                        setMenu     = { this.props.setMenu }
                    />
                    <Comments
                        animeId     = { data.anime._id.$oid }
                        episode     = { data.episode }
                    /></>
                : null
            }
            {!loaded
                ? <Loader/>
                : null
            }
        </>
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

    constructor(props) {
        super(props);

        this.state = {
            completing: false,
            completed: this.props.isWatched,
            showRollbackForm: false,
            showEpisodeSnackbar: false
        }
    }

    componentDidUpdate( prevProps ) {
        if(prevProps.isWatched !== this.props.isWatched) {
            this.setState({completed: this.props.isWatched})
        }
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
            onUpdate        = { this.props.onUpdate }
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
            nextEpisode
        } = this.props;

        const {
            completed
        } = this.state;

        if(!canComplete || this.state.completing) {
            return;
        }

        if(completed) {
            this.setState({showRollbackForm: true});
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
            this.props.onUpdate();

            if(nextEpisode !== undefined && nextEpisode !== null) {
                history.push(`/v/${nextEpisode}`);
            } else
            {
                this.setState({showEpisodeSnackbar: true})
            }
        })
    }

    rollbackEpisode() {
        const {
            data: {
                anime: {
                    _id
                },
                episode,
            }
        } = this.props;

        this.setState({
            completing: true,
            showRollbackForm: false
        });

        fetch(
            "user/episode/watched",
            {
                "anime_id" : _id.$oid,
                "episode"  : episode - 1
            },
            "PUT"
        ).then(() => {
            this.setState({
                completing: false,
                completed: false
            });
            this.props.onUpdate();
        })
    }

    renderButtons() {
        const {
            completed
        } = this.state;
        const {data: {episode}} = this.props;

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
            <span
                style={{
                    marginRight: "5px 15px",
                    color: "#8a8a8a"
                }}
            >
                {episode} эпизод
            </span>
            <Button
                variant     = {completed ? "text" : "contained"}
                color       = {completed ? "primary" : "secondary"}
                onClick     = {this.bumpEpisode.bind(this)}
                startIcon   = {<CheckIcon/>}
                style       = {{float: "right"}}
            >
                <span className="hide-630px">
                    {completed ? "Просмотрена" : "Отметить просмотренной"}
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
        const {data} = this.props;
        const {
            showRollbackForm,
            showEpisodeSnackbar
        } = this.state;

        return <div style={this.styles.root}>
            <RollbackEpisodeDialog
                completed   = {data.last_watched_episode}
                current     = {data.episode}
                open        = {showRollbackForm}
                onClose     = {()=>this.setState({showRollbackForm: false})}
                onRollback  = {this.rollbackEpisode.bind(this)}
            />
            <OngoingEpisodeSnackbar
                open            = {showEpisodeSnackbar}
                current         = {data.episode}
                onClose         = {()=>this.setState({showEpisodeSnackbar: false})}
            />
            { this.renderButtons() }
            { this.renderAnimeInfo() }
        </div>
    }
}

function RollbackEpisodeDialog({completed, current, open, onClose, onRollback}) {
    return <Dialog
        open={open}
        onClose={onClose}
        aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description"
    >
        <DialogTitle id="alert-dialog-title">{"Откатить просмотренное?"}</DialogTitle>
        <DialogContent>
            <DialogContentText id="alert-dialog-description">
                Если вы откатите количество просмотренных серий,
                то ваш прогресс вернется с {completed} серии на {current - 1}.<br/>
                Все равно откатить?
            </DialogContentText>
        </DialogContent>
        <DialogActions>
            <Button onClick={onClose} color="primary">
                Нет
            </Button>
            <Button onClick={onRollback} color="primary" autoFocus>
                Да
            </Button>
        </DialogActions>
    </Dialog>
}

function OngoingEpisodeSnackbar({open, current, onClose}) {
    return <Snackbar
        open             = { open }
        autoHideDuration = { 6000 }
        onClose          = { onClose }
        anchorOrigin={{
            "vertical"   : "top",
            "horizontal" : "tight"
        }}
        key="top,right"
    >
        <Alert severity="success">
            {current} эпизод отмечен как просмотренный. {current + 1} эпизод пока не выпущен.
        </Alert>
    </Snackbar>
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