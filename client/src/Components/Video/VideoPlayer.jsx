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
import RecordVoiceOverIcon from "@material-ui/icons/RecordVoiceOver";
import SubtitlesIcon from "@material-ui/icons/Subtitles";
import TranslateIcon from "@material-ui/icons/Translate";
import IconButton from "@material-ui/core/IconButton";
import ChevronLeftIcon from '@material-ui/icons/ChevronLeft';
import ChevronRightIcon from '@material-ui/icons/ChevronRight';
import Achieve from "../Achievement/Achieve";
import EditIcon from '@material-ui/icons/Edit';
import AddIcon from "@material-ui/icons/Add";
import Tooltip from "@material-ui/core/Tooltip";
import TextField from "@material-ui/core/TextField";
import FormControl from "@material-ui/core/FormControl";
import Select from "@material-ui/core/Select";
import MenuItem from "@material-ui/core/MenuItem";
import FormHelperText from "@material-ui/core/FormHelperText";
import Avatar from "@material-ui/core/Avatar"
import {withRouter} from 'react-router-dom';

moment.locale("ru");

export default class VideoPlayer extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            "showLogin": alreadyShowed("login"),
            "showLogout": alreadyShowed("logout"),
            "data": null,
            "notFound": false,
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
            .then(
                (result) => {
                    this.setState({ "loaded": true, "data": result.data });
                },
                (result) => {
                    this.setState({loaded: true, data: result, notFound: true})
                });

    }

    fetchWithoutLoader() {
        this.fetch(false);
    }

    renderCommon() {
        const  {
            showLogin,
            showLogout,
            data
        } = this.state;

        return <>
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
            <BackgroundPoster poster = {process.env.REACT_APP_CDN_BASE + data.anime.poster.original}>
                <EpisodeName
                    name    = {data.name || 'Эпизод без имени'}
                />
                <VideoPlayerIframe url={data.url}/>
            </BackgroundPoster>
            <Toolbar
                canComplete = { data.user !== null }
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
    }

    render () {

        const {
            loaded,
            data,
            notFound
        } = this.state;

        if (loaded && data.user === undefined && !alreadyShowed("auth")) {

            setShow("auth");
            window.location.href = `https://auth.todonime.ru/?back_url${window.location}`;

        }

        return <>
            {data !== null
                ? !notFound ? this.renderCommon() : <NotFound />
                : null
            }
            {!loaded
                ? <Loader/>
                : null
            }
        </>
    }
}

function BackgroundPoster({poster, children}) {
    const styles = {
        root: {
            backgroundImage: `url(${poster})`,
            backgroundPosition: 'center',
            backgroundSize: 'cover'
        },
        blur: {
            width: '100%',
            backdropFilter: "blur(8px)",
            background: 'rgba(0, 0, 0, 0.2)',
        }
    };

    return <div style={styles.root}>
        <div style={styles.blur}>
            {children}
        </div>
    </div>
}

function NotFound() {
    const styles = {
        root: {
            width: "100%",
            height: "calc(100vh - 160px)",
            display: "flex"
        },
        content: {
            margin: "auto",
            color: "white"
        }
    }

    return <div style={styles.root}>
        <div style={styles.content}>
            Видео не найдено
        </div>
    </div>
}


function EpisodeName({name}) {
    const styles = {
        root: {
            color: "white",
            display: "flex",
            padding: "10px 0",
            maxWidth: "75vw",
            margin: "auto"
        },
        name: {
            margin: "auto 0",
            lineHeight: 1,
        }
    };

    return <div style={styles.root}>
        <span style={styles.name}>{name}</span>
    </div>
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
            padding: "15px",
            background: "white"
        },
        buttons: {
            display: 'flex',
            maxWidth: "958px",
            margin: "auto",
            justifyContent: "center"
        },
        animeInfo: {
            maxWidth: "950px",
            margin: "auto",
            marginTop: "10px"
        },
        animeData: {
            marginLeft: '5px'
        },
        authorInfo: {
            marginBottom: "15px",
            display: "flex",
            alignItems: 'center'
        },
        authorInfoText: {
            marginLeft: "5px"
        },
        uploader: {
            fontSize: '12px',
            color: '#898989',
            marginLeft: '5px',
            display: 'inline-flex'
        }
    }

    constructor(props) {
        super(props);

        this.state = {
            completing          : false,
            completed           : this.props.isWatched,
            showRollbackForm    : false,
            showEpisodeSnackbar : false,
            showAuthConfirm     : false
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

        if(!canComplete) {
            this.setState({showAuthConfirm: true});
            return;
        }

        if(this.state.completing) {
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

            if(nextEpisode !== undefined && nextEpisode !== null) {
                history.push(`/v/${nextEpisode}`);
            } else
            {
                this.props.onUpdate();
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

    onBackEpisode() {
        const {
            data: {
                prev_episode
            },
            history
        } = this.props;

        if(prev_episode !== null && prev_episode.video_id !== undefined) {
            history.push(`/v/${prev_episode.video_id}`);
        }
    }

    onNextEpisode() {
        const {
            data: {
                next_episode
            },
            history
        } = this.props;

        if(next_episode !== null && next_episode.video_id !== undefined) {
            history.push(`/v/${next_episode.video_id}`);
        }
    }

    renderButtons() {
        const {
            canComplete,
        } = this.props;
        const {
            completed
        } = this.state;
        const {data: {
            episode,
            name,
            anime,
            projects
        }} = this.props;

        return <div style={this.styles.buttons}>
            <div style={{margin: "auto 0"}}>
                <Button
                    onClick     = {this.onOpenList.bind(this)}
                    startIcon   = {<TheatersIcon/>}
                >
                    <span className="hide-630px">Переводы</span>
                </Button>
                <AddVideoWithRouter
                    animeId = {anime._id.$oid}
                    animeName = {anime.name_ru || anime.name_en}
                    episode = {episode}
                    projects = {projects}
                    onConfirm = {this.props.onUpdate}
                />
            </div>
            <Tooltip title="Предыдущий эпизод">
                <IconButton onClick={this.onBackEpisode.bind(this)}>
                    <ChevronLeftIcon/>
                </IconButton>
            </Tooltip>
            <div style={{margin: "auto 0"}}>
                <Button
                    onClick     = {this.onOpenAnimeInfo.bind(this)}
                    startIcon   = {<ViewListIcon/>}
                >
                    <span className="hide-630px">Эпизоды</span>
                </Button>
                <span
                    style={{
                        margin: "11px 5px",
                        color: "#8a8a8a"
                    }}
                >
                {episode} эпизод
                </span>
                <UpdateEpisode
                    episode = {episode}
                    name    = {name}
                    animeId = {anime._id.$oid}
                    onUpdate= {this.props.onUpdate}
                />
            </div>
            <Tooltip title="Следующий эпизод">
                <IconButton onClick={this.onNextEpisode.bind(this)}>
                    <ChevronRightIcon/>
                </IconButton>
            </Tooltip>
            <div style={{margin: "auto 0 auto auto"}}>
                <Button
                    variant     = {completed ? "text" : "contained"}
                    color       = {completed ? "primary" : canComplete ? "secondary" : "disabled"}
                    onClick     = {this.bumpEpisode.bind(this)}
                    startIcon   = {<CheckIcon/>}
                    style       = {{marginLeft: "auto"}}
                >
                    <span className="hide-630px">
                        {completed ? "Просмотрена" : "Отметить просмотренной"}
                    </span>
                </Button>
            </div>
        </div>
    }

    renderAnimeInfo() {
        const {
            data
        } = this.props;

        const icon = {
            'dub': <RecordVoiceOverIcon fontSize="small"/>,
            'sub': <SubtitlesIcon fontSize="small" />,
            'org': <TranslateIcon fontSize="small" />
        }[ data.kind ] || <RecordVoiceOverIcon />;

        return <div style={this.styles.animeInfo}>
            <div style={this.styles.authorInfo}>
                <img style={{"marginRight": "5px",
                    "verticalAlign": "middle"}}
                     src={`https://www.google.com/s2/favicons?domain=${data.domain}`}
                     alt={data.domain}
                     title={data.domain}
                />
                {icon}
                <div style={this.styles.authorInfoText}>
                    {data.author}
                    {data.uploader
                        ? <span style={this.styles.uploader}>
                            <span style={{margin: "auto 0"}}>
                                {'загрузил' + (data.uploader.sex === 'female' ? 'а' : '')}
                            </span>
                            <Avatar
                                variant="rounded"
                                src={data.uploader.avatar}
                                style={{
                                    'margin': '0 5px',
                                    'width': '16px',
                                    'height': '16px',
                                    'display': 'inline-block'
                                }}
                            />
                            <span style={{margin: "auto 0"}}>{data.uploader.nickname}</span>
                        </span>
                        : null
                    }
                </div>
            </div>
            <AnimeCard
                anime = {data.anime}
                currentEpisode  = {data.episode}
                lastEpisode     = {data.last_watched_episode}
            />
        </div>
    }

    login() {
        setShow('login');
        window.location.href = `${process.env.REACT_APP_AUTH_BASE}?back_url=${window.location}`;
    }

    render() {
        const {data} = this.props;
        const {
            showRollbackForm,
            showEpisodeSnackbar,
            showAuthConfirm
        } = this.state;

        return <div style={this.styles.root}>
            <ConfirmAuthDialog
                open        = {showAuthConfirm}
                onClose     = {()=>this.setState({showAuthConfirm: false})}
                onConfirm   = {this.login.bind(this)}
            />
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
            {data.user !== null && data.user.nickname === 'Андрей Чурик'
                ? <div
                    style={{
                        maxWidth: "808px",
                        margin: "auto",
                        display: 'flex'
                    }}
                >
                    <Achieve/>
                </div>
                : null
            }
            { this.renderAnimeInfo() }
        </div>
    }
}

class AddVideo extends React.Component {

    state = {
        open        : false,
        showSuccess : false
    }

    onConfirm(videoId) {
        this.setState({
            open        : false,
            showSuccess : true,
            videoId
        });
        this.props.onConfirm();
    }

    toVideo() {
        const {
            videoId
        } = this.state;

        this.props.history.push(`/v/${videoId}`);
    }

    render() {
        const {
            showSuccess
        } = this.state;

        return <>
            <Snackbar
                open={showSuccess}
                autoHideDuration={6000}
                onClose={()=>this.setState({showSuccess: false})}
            >
                <Alert onClose={()=>this.setState({showSuccess: false})} severity="success">
                    Видео добавлено!
                    <Button
                        onClick={this.toVideo.bind(this)}
                        variant="outlined"
                        style={{marginLeft: "15px", color: 'white'}}
                        size="small"
                    >
                        перейти
                    </Button>
                </Alert>
            </Snackbar>
            <AddVideoDialog
                animeId     = {this.props.animeId}
                episode     = {this.props.episode}
                animeName   = {this.props.animeName}
                projects    = {this.props.projects}
                open        = {this.state.open}
                onClose     = {()=>this.setState({open:false})}
                onConfirm   = {this.onConfirm.bind(this)}
            />
            <Tooltip title="Добавить перевод.">
                <IconButton onClick={()=> this.setState({open: true})}>
                    <AddIcon style={{width: "16px", height: "16px"}}/>
                </IconButton>
            </Tooltip>
        </>
    }
}

const AddVideoWithRouter = withRouter(AddVideo);

class AddVideoDialog extends React.Component {

    state = {
        url     : null,
        kind    : 'dub',
        lang    : 'ru',
        author  : ''
    }

    send() {
        const {
            url,
            kind,
            lang,
            author
        } = this.state;
        const {
            animeId,
            episode,
            onConfirm
        } = this.props;

        fetch(
            'video',
            {
                url,
                kind,
                lang,
                author,
                episode,
                anime_id: animeId
            },
            'PUT'
        ).then((data) => onConfirm(data.data.video_id.$oid));
    }

    renderForm() {
        const {
            kind, lang, author
        } = this.state;

        return <>
            <div style={{display: 'flex'}}>
                <FormControl>
                    <Select
                        labelId="kind-helper-text"
                        value={kind}
                        displayEmpty
                        onChange={(e) =>
                            this.setState({
                                kind: e.target.value,
                                lang: e.target.value === 'org' ? 'jp' : lang
                            })
                        }
                    >
                        <MenuItem value="dub">озвучка</MenuItem>
                        <MenuItem value="sub">субтитры</MenuItem>
                        <MenuItem value="org">оригинал</MenuItem>
                    </Select>
                    <FormHelperText>Тип</FormHelperText>
                </FormControl>
                <FormControl style={{marginLeft: "15px"}}>
                    <Select
                        labelId="lang-helper-text"
                        value={lang}
                        displayEmpty
                        onChange={(e) => this.setState({lang: e.target.value}) }
                    >
                        <MenuItem value="ru">ru</MenuItem>
                        <MenuItem value="en">en</MenuItem>
                        <MenuItem value="jp">jp</MenuItem>
                    </Select>
                    <FormHelperText>Язык</FormHelperText>
                </FormControl>
                <FormControl style={{marginLeft: "15px", flex: 1}}>
                    <TextField
                        fullWidth   = {true}
                        helperText  = "Ссылка на embed"
                        placeholder = "https://ok.ru/videoembed/89366202942"
                        onChange    = {(e) => this.setState({url: e.target.value})}
                        InputProps={{
                            style: {
                                width: "100%"
                            }
                        }}
                    />
                </FormControl>
            </div>
            <FormControl style={{display: "flex", marginTop: "15px"}}>
                <TextField
                    fullWidth   = {true}
                    helperText  = "Авторы"
                    placeholder = "Ника Ленина & Shashiburi"
                    value       = {author}
                    onChange    = {(e) => this.setState({author: e.target.value})}
                    InputProps={{
                        style: {
                            width: "100%"
                        }
                    }}
                />
            </FormControl>
        </>
    }

    render() {
        const {
            open,
            onClose,
            episode,
            animeName
        } = this.props;

        return <Dialog
            open            = {open}
            onClose         = {onClose}
            aria-labelledby = "alert-dialog-title"
            aria-describedby= "alert-dialog-description"
        >
            <DialogTitle id="alert-dialog-title">
                {"Добавить видео к " + episode + " эпизоду " + animeName}
            </DialogTitle>
            <DialogContent>
                <DialogContentText
                    id="alert-dialog-description"
                    style={{width: "450px"}}
                >
                    {this.renderForm()}
                </DialogContentText>
            </DialogContent>
            <DialogActions>
                <Button onClick={this.props.onClose} color="primary">
                    отмена
                </Button>
                <Button onClick={this.send.bind(this)} color="primary" autoFocus>
                    добавить
                </Button>
            </DialogActions>
        </Dialog>
    }
}

class UpdateEpisode extends React.Component {

    state = {
        open: false
    };

    openDialog() {
        this.setState({open: true})
    }

    onSave(name) {
        fetch(`anime/${this.props.animeId}/episode/name`, {
            episode: this.props.episode,
            name
        }, 'POST')
            .then(() => {
                this.setState({open: false})
                this.props.onUpdate()
            });
    }

    render() {
        return <>
            <UpdateEpisodeDialog
                episode = {this.props.episode}
                name = {this.props.name}
                open={this.state.open}
                onClose={()=>this.setState({open:false})}
                onConfirm={this.onSave.bind(this)}
            />
            <Tooltip title="Изменить параметры серии">
                <IconButton onClick={this.openDialog.bind(this)}>
                    <EditIcon style={{width: "16px", height: "16px"}}/>
                </IconButton>
            </Tooltip>
        </>
    }
}

function UpdateEpisodeDialog({open, episode, name, onClose, onConfirm}) {
    const ref = React.createRef();

    const onSave = () => {
        onConfirm(ref.current.value);
    }

    return <Dialog
        open            = {open}
        onClose         = {onClose}
        aria-labelledby = "alert-dialog-title"
        aria-describedby= "alert-dialog-description"
    >
        <DialogTitle id="alert-dialog-title">{"Изменение " + episode + " эпизода."}</DialogTitle>
        <DialogContent>
            <DialogContentText id="alert-dialog-description" style={{width: "450px"}}>
                <TextField
                    inputRef    = {ref}
                    fullWidth   = {true}
                    helperText  = "Название эпизода"
                    placeholder = "Название эпизода"
                    defaultValue= {name}
                    InputProps  = {{
                        width: "350px"
                    }}
                />
            </DialogContentText>
        </DialogContent>
        <DialogActions>
            <Button onClick={onClose} color="primary">
                отмена
            </Button>
            <Button onClick={onSave} color="primary" autoFocus>
                сохранить
            </Button>
        </DialogActions>
    </Dialog>
}

function ConfirmAuthDialog({open, onClose, onConfirm}) {
    return <Dialog
        open            = {open}
        onClose         = {onClose}
        aria-labelledby = "alert-dialog-title"
        aria-describedby= "alert-dialog-description"
    >
        <DialogTitle id="alert-dialog-title">{"Авторизироваться через shikimori.one"}</DialogTitle>
        <DialogContent>
            <DialogContentText id="alert-dialog-description">
                Для хранения вашего просмотреного мы используем shikimori.one. Поэтому для отметки нужно
                авторизироваться.<br/>
                Перейти к авторизации? (2 клика).
            </DialogContentText>
        </DialogContent>
        <DialogActions>
            <Button onClick={onClose} color="primary">
                Нет
            </Button>
            <Button onClick={onConfirm} color="primary" autoFocus>
                Да
            </Button>
        </DialogActions>
    </Dialog>
}

export function RollbackEpisodeDialog({completed, current, open, onClose, onRollback}) {
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