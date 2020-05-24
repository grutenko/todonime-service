import React from "react";
import {fetch} from "../../lib/api";
import {IconButton, withStyles} from "@material-ui/core";
import CircularProgress from "@material-ui/core/CircularProgress";
import Paper from "@material-ui/core/Paper";
import moment from "moment";
import Avatar from "@material-ui/core/Avatar";
import Chip from "@material-ui/core/Chip";
import TextField from "@material-ui/core/TextField";
import InputAdornment from "@material-ui/core/InputAdornment";
import AddCommentIcon from "@material-ui/icons/AddComment";
import SentimentSatisfiedIcon from "@material-ui/icons/SentimentSatisfied";
import ClickAwayListener from "@material-ui/core/ClickAwayListener";
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import DeleteIcon from '@material-ui/icons/Delete';

import Menu from '@material-ui/core/Menu';
import MenuItem from "@material-ui/core/MenuItem";
import ListItemIcon from '@material-ui/core/ListItemIcon';
import ListItemText from '@material-ui/core/ListItemText';

import "emoji-mart/css/emoji-mart.css";
import {Picker} from "emoji-mart";

export default class Comments extends React.Component {

    /**
     * @type {{root: {padding: string, backgroundColor: string, margin: string}}}
     */
    styles = {
        "root": {
            "backgroundColor": "white",
            "margin": "24px 0",
            "padding": "24px"
        }
    };

    /**
     * @type {{comments: ?array, load: boolean, user: ?array}}
     */
    state = {
        load        : true,
        comments    : null,
        user        : null
    };

    /**
     * @var {{animeId: int, episode: int}} props
     */

    componentDidMount() {
        this.__fetch();
    }

    /**
     * Отправляет запрос на добавление нового комментария.
     * @param {string} text
     */
    addComment(text) {
        const {
                animeId,
                episode
            } = this.props,
            {
                comments
            } = this.state;

        fetch(
            "video/comments",
            { "anime_id": animeId, episode, text },
            "POST"
        ).then(({data}) => {
            this.setState({
                "comments"  : [ data, ...comments],
            })
        });
    }

    /**
     * Делает запрос к серверу и сохраняет комментарии в состояние.
     * @private
     */
    __fetch() {
        const {
                animeId,
                episode
            } = this.props;

        this.setState({load: true});
        fetch(
            "video/comments",
            {
                "anime_id": animeId,
                episode
            }
        ).then(({data}) => this.setState({
            comments    : data.comments,
            user        : data.user,
            load        : false
        }));
    }

    /**
     * @param commentId
     */
    delete(commentId) {
        fetch("video/comments/"+commentId, {}, 'DELETE')
            .then(data => {
                this.setState({
                    comments: this.state.comments.filter(comment => comment._id.$oid !== commentId)
                })
            });
    }

    render() {
        const {
                load,
                comments,
                user
            } = this.state,
            {
                animeId,
                episode
            }= this.props;

        return <div style={this.styles.root}>
            {load
                ? <CircularProgress color="inherit" />
                : <CommentsBlock
                    animeId     = { animeId }
                    episode     = { episode }
                    currentUser = { user }
                    comments    = { comments }
                    onSubmit    = { this.addComment.bind(this) }
                    onDelete    = { this.delete.bind(this) }
                />
            }
        </div>;
    }
}

class CommentsBlock extends React.Component {

    /**
     * @type {{root: {margin: string, maxWidth: string}}}
     */
    styles = {
        "root": {
            "maxWidth": "800px",
            "margin": "auto"
        }
    }

    render() {
        const {
            animeId,
            currentUser,
            comments,
            onSubmit
        } = this.props;

        return <div style={this.styles.root}>
            <h3>Комментарии</h3>
            {currentUser !== null
                ? <CommentForm
                    animeId     = {animeId}
                    user        = {currentUser}
                    onSubmit    = {onSubmit}
                />
                : null
            }
            { comments.map(comment => {
                return <Comment
                    key={comment._id.$oid}
                    comment={comment}
                    user={currentUser}
                    onDelete={this.props.onDelete}
                />
            }) }
            { comments.length === 0
                ? <NotFoundComment needAuth={ currentUser === null }/>
                : null}
        </div>
    }
}

/**
 * @param {object} comment
 * @param {object} user
 * @param onDelete
 * @returns {*}
 * @constructor
 */
function Comment ({comment, user, onDelete}) {

    /**
     * @var {{
     *      created_at: {$date: {$numberLong: string}},
     *      user: object
     *      text: string
     * }} comment
     */

    /**
     * @type {{
     *      item: {padding: string, margin: string, display: string}
     *      avatar: {marginTop: string}
     * }}
     */
    const styles = {
        "item": {
            "display": "flex",
            "padding": "5px 15px",
            "margin": "0 0 15px 0"
        },
        "avatar": {
            "marginTop": "15px"
        }
    };

    const [
        hover,
        setHover
    ] = React.useState(false);

    return <Paper
        variant      = "outlined"
        style        = {styles.item}
        onMouseEnter = {()=>setHover(true)}
        onMouseLeave = {()=>setHover(false)}
    >
        <Avatar
            style   ={styles.avatar}
            alt     = {comment.user.nickname}
            src     = {comment.user.avatar}
        />
        <CommentText
            onMenuClick = {action => onDelete(comment._id.$oid)}
            showMenu    = {hover}
            text        = {comment.text}
            user        = {comment.user}
            createdAt   = {parseInt(
                comment.created_at.$date.$numberLong
            )}
        />
    </Paper>
}

const StyledMenu = withStyles({
    paper: {
        border: '1px solid #d3d4d5',
    },
})((props) => (
    <Menu
        elevation={0}
        getContentAnchorEl={null}
        anchorOrigin={{
            vertical: 'bottom',
            horizontal: 'center',
        }}
        transformOrigin={{
            vertical: 'top',
            horizontal: 'center',
        }}
        {...props}
    />
));

function CommentMenu({ show, onClick }) {
    const [anchorEl, setAnchorEl] = React.useState(null);

    const handleClick = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    return <span>
        <IconButton
            style           = {!show ? {opacity: '0'} : {}}
            aria-controls   = "simple-menu"
            aria-haspopup   = "true"
            disabled        = {!show}
            onClick         = {handleClick}
        >
            <ExpandMoreIcon />
        </IconButton>
        <StyledMenu
            id="customized-menu"
            anchorEl={anchorEl}
            keepMounted
            open={Boolean(anchorEl) && show}
            onClose={handleClose}
        >
            <MenuItem onClick={()=> {
                onClick('delete');
            }}>
              <ListItemIcon>
                <DeleteIcon fontSize="small" />
              </ListItemIcon>
              <ListItemText primary="Удалить" />
            </MenuItem>
        </StyledMenu>
    </span>
}

function NotFoundComment({needAuth}) {
    /**
     * @type {{
     *      item: {padding: string, margin: string, display: string},
     *      notFound: {margin: string, padding: string, textAlign: string}
     * }}
     */
    const styles = {
        "item": {
            "display": "flex",
            "padding": "15px",
            "margin": "0 0 15px 0"
        },
        "notFound": {
            "margin": "auto",
            "padding": "200px 40px",
            "textAlign": "center"
        },
        "link": {
            "fontSize": "inherit"
        }
    };

    return <Paper variant="outlined" style={styles.item}>
        <div style={styles.notFound}>
            Еще нет комментариев.
            {needAuth
                ? <>
                    <br/>
                    <a href="https://auth.todonime.ru" style={styles.link}>
                        Авторизируйтесь
                    </a> и напишите свой.
                </>
                : null}
        </div>
    </Paper>
}

function CommentText ({showMenu, text, user, createdAt, onMenuClick}) {

    /**
     *
     * @type {{
     *      nickname: {marginRight: string, "font-weight": string, "line-height": string},
     *      text: {margin: string, flex: number},
     *      time: {color: string, fontSize: string, "line-height": string, "marginRight": string}
     * }}
     */
    const styles = {
        "text": {
            "margin": "0 0 0 15px",
            "flex": 1
        },
        "nickname": {
            "marginRight": "5px",
            "font-weight": "bold",
            "line-height": "1"
        },
        "time": {
            "fontSize": "12px",
            "color": "#949494",
            "line-height": "1.5",
            "marginRight": "15px"
        }
    }

    return <div style={styles.text}>
        <div>
            <span style={styles.nickname}>
                {user.nickname}<Scopes scopes={user.scope}/>
            </span>
            <time style={styles.time}>{ moment(createdAt).fromNow() }</time>
            <CommentMenu show={showMenu} onClick={onMenuClick} />
        </div>
        <pre>{ text }</pre>
    </div>
}

function Scopes({scopes}) {

    /**
     * @type {{chip: {height: string, marginLeft: string}}}
     */
    const styles = {
        "chip": {
            "height": "20px",
            "marginLeft": "5px"
        }
    };

    return <>
        {scopes.map(scope => <Chip
            style={styles.chip}
            color="primary"
            label={scope}
        />)}
    </>
}

class CommentForm extends React.Component {

    /**
     * @type {{
     *      root: {width: string, marginBottom: string},
     *      dropdown: {top: number, left: number, position: string, right: number, zIndex: number}
     * }}
     */
    formClasses = {
        "root": {
            "width": "100%",
            "marginBottom": "15px"
        },
        "dropdown": {
            "position": "absolute",
            "top": 28,
            "right": 0,
            "left": 0,
            "zIndex": 1
        }
    };

    /**
     * @type {React.RefObject<Element>}
     */
    inputRef = React.createRef();

    /**
     * @type {{pickerShow: boolean}}
     */
    state = {
        "pickerShow": false
    };

    /**
     * @param _prevProps
     * @param prevState
     */
    componentDidUpdate (_prevProps, prevState) {

        if (prevState.pickerShow !== this.state.pickerShow) {

            if (this.state.pickerShow && this.inputRef.current !== document.activeElement) {

                this.inputRef.current.focus();

            }

        }

    }

    togglePicker() {
        this.setState({
            pickerShow: !this.state.pickerShow
        });
    }

    showPicker() {
        this.setState({pickerShow: true});
    }

    hidePicker() {
        this.setState({pickerShow: false});
    }

    setEmoji(emoji) {
        if( this.inputRef.current !== document.activeElement ) {

            this.inputRef.current.focus();

        }
        const val = this.inputRef.current.value;
        const pos = this.inputRef.current.selectionStart;

        this.inputRef.current.value = val.slice(0, pos) + emoji.native + val.slice(pos + 1);
    }

    onKeyUp(e) {
        if( this.inputRef.current === document.activeElement ) {
            const val = this.inputRef.current.value.trim();

            if( e.ctrlKey && e.keyCode === 13 ) {
                if( val.length > 0) {
                    this.props.onSubmit(val);
                    this.inputRef.current.value = "";
                }
            }
        }
    }

    render () {
        const {
            pickerShow
        } = this.state;

        const inputProps = {
            inputRef        : this.inputRef,
            inputProps      : {
                onKeyUp: this.onKeyUp.bind(this)
            },
            startAdornment  : <CommentIcon />,
            endAdornment    : <Emoji
                show     = {pickerShow}
                onToggle = {this.togglePicker.bind(this)}
                onSelect = {this.setEmoji.bind(this)}
            />
        };

        return <ClickAwayListener
            onClickAway={this.hidePicker.bind(this)}
        >
            <TextField
                style       = {this.formClasses.root}
                id          = "outlined-multiline-flexible"
                label       = "Ctrl+Enter - отправить"
                multiline
                rowsMax     = {8}
                onChange    = { () => {} }
                variant     = "outlined"
                InputProps  = {inputProps}
            />
        </ClickAwayListener>
    }

}

const CommentIcon = () =>
    <InputAdornment position="start">
        <AddCommentIcon color="primary" />
    </InputAdornment>,

    Emoji = ({show, onToggle, onSelect}) => <InputAdornment position="end">
        <IconButton>
            <SentimentSatisfiedIcon
                color="primary"
                onClick={onToggle}
            />
        </IconButton>
        {show
            ? <Picker
                set="google"
                style={{
                    "position": "absolute",
                    "top": 70,
                    "right": 0,
                    "left": 0,
                    "zIndex": 1
                }}
                onSelect={onSelect}
            />
            : null
        }
    </InputAdornment>;