import React from "react";

import Loader from "../Misc/Loader";

import moment from "moment";
import "moment/locale/ru";
import {fetch as __fetch} from "../../lib/api";
import VideoPlayerIframe from "./VideoPlayerIframe";
import {IconButton, withStyles} from "@material-ui/core";
import ViewListIcon from "@material-ui/icons/ViewList";
import KeyboardArrowLeftIcon from "@material-ui/icons/KeyboardArrowLeft";
import KeyboardArrowRightIcon from "@material-ui/icons/KeyboardArrowRight";
import TheatersIcon from "@material-ui/icons/Theaters";
import Popper from "@material-ui/core/Popper";
import {drawerWidth} from "../Menu";
import clsx from "clsx";
import VideosList from "./VideosList";
import AnimeInfo from "../Anime/AnimeInfo";
import {Link, Redirect} from "react-router-dom";
import Button from "@material-ui/core/Button";
import ButtonPopper from "../Misc/ButtonPopper";
import BeenhereIcon from "@material-ui/icons/Beenhere";
import { alreadyShowed, setShow, unsetShow } from "../../lib/promt";
import Snackbar from '@material-ui/core/Snackbar';
import MuiAlert from '@material-ui/lab/Alert';

function Alert(props) {
  return <MuiAlert elevation={6} variant="filled" {...props} />;
}

moment.locale("ru");

class VideoPlayer extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            "showLogin": alreadyShowed('login'),
            "showLogout": alreadyShowed('logout'),
            "data": null,
            "loaded": false,
            "rightMenuPortal": false
        };

        if(this.state.showLogin) {
            unsetShow('login');
        }
        if(this.state.showLogout) {
            unsetShow('logout');
        }
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
            .then((result) => {

                this.setState({
                    "loaded": true,
                    // eslint-disable-next-line sort-keys
                    "data": result.data
                });

                const kind = {
                    "dub": "озвучка",
                    "sub": "субтитры",
                    "org": "оригинал"
                }[result.data.kind] || "озвучка";

                this.props.setTitle(<>
                    <Button
                        variant="contained"
                        color="primary"
                        onClick={() => window.open(`https://shikimori.one${result.data.anime.url}`)}
                    >
                        {result.data.anime.name_ru || result.data.anime.name_en}
                    </Button>
                    <span style={{"margin": "auto 5px"}}>{result.data.episode} серия</span>
                </>);

            });

    }

    onOpenTranslationsList () {

        this.props.setMenu(<VideosList
            setMenu={this.props.setMenu}
            currentId={this.props.match.params.id}
            currentKind={this.state.data.kind}
            videos={this.state.data.videos}
        />);

    }

    onOpenAnimeInfo () {

        this.props.setMenu(<AnimeInfo anime={this.state.data.anime} />);

    }

    bumpEpisode () {

        if (this.state.data.user) {

            __fetch(
                "user/episode/watched",
                {
                    "anime_id": this.state.data.anime._id.$oid,
                    "episode": this.state.data.episode
                },
                "PUT"
            ).then((data) => {

                this.props.history.push(`/v/${this.state.data.next_episode.video_id}`);

            });

        }

    }

    renderRightToolbar () {

        const {data} = this.state,
            {menuOpen} = this.props;

        return <div
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
            <IconButton>
                <TheatersIcon onClick={this.onOpenAnimeInfo.bind(this)} />
            </IconButton><br/>
            <IconButton>
                {data.prev_episode !== null
                    ? <Link onClick={() => {

                        this.props.setMenu(null);

                    }} to={`/v/${data.prev_episode.video_id}`}>
                        <KeyboardArrowLeftIcon />
                    </Link>
                    : <KeyboardArrowLeftIcon />
                }
            </IconButton><br/>
            <IconButton>
                <BeenhereIcon
                    color={this.state.data.user ? "secondary" : "disabled"}
                    onClick={this.bumpEpisode.bind(this)}
                />
            </IconButton><br/>
            {data.next_episode !== null
                ? data.next_episode.video_id != null
                    ? <IconButton><Link onClick={() => {

                        this.props.setMenu(null);

                    }} to={`/v/${data.next_episode.video_id}`}>
                        <KeyboardArrowRightIcon />
                    </Link></IconButton>
                    : data.next_episode.next_episode_at != null
                        ? <ButtonPopper
                            text={`${data.episode + 1} серия через ${moment(parseInt(data.next_episode.next_episode_at)).fromNow(true)}`}
                        >
                            <KeyboardArrowRightIcon />
                        </ButtonPopper>
                        : <IconButton><KeyboardArrowRightIcon /></IconButton>
                : <IconButton><KeyboardArrowRightIcon /></IconButton>
            }
        </div>;

    }

    // eslint-disable-next-line class-methods-use-this
    render () {

        const {loaded, data, redirectToNext, showLogin, showLogout} = this.state;

        if(loaded && data.user === undefined && !alreadyShowed('auth')) {
            setShow('auth');
            window.location.href = 'https://auth.todonime.ru/?back_url'+ window.location;
        }

        // eslint-disable-next-line no-ternary
        return <>
            {loaded
                ? <>
                    <Snackbar
                        open={showLogin}
                        autoHideDuration={6000}
                        onClose={() => this.setState({showLogin: false})}
                        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
                        key="top,right"
                    >
                        <Alert severity="info">
                            Вы успешно авторизировались через shikimori.one
                        </Alert>
                    </Snackbar>
                    <Snackbar
                        open={showLogout}
                        autoHideDuration={6000}
                        onClose={() => this.setState({showLogout: false})}
                        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
                        key="top,right"
                    >
                        <Alert severity="info">
                            Вы успешно вышли из аккаунта
                        </Alert>
                    </Snackbar>
                    {redirectToNext
                        ? <Redirect to={`/v/${data.next_episode.video_id}`}/>
                        : null
                    }
                    <VideoPlayerIframe url={data.url}/>
                    {this.renderRightToolbar()}
                </>
                : <Loader/>}
        </>;

    }

}

const styles = (theme) => ({
    "fixedToolBox": {
        "background": "white",
        "margin": "13px 11px",
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


