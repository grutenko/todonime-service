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
import EditIcon from '@material-ui/icons/Edit';
import CloseIcon from '@material-ui/icons/Close';
import SendIcon from '@material-ui/icons/Send';

import "emoji-mart/css/emoji-mart.css";
import {Picker} from "emoji-mart";
import {createWs} from "../../lib/ws";

import './Comments.css';

export default class Comments extends React.Component {

    /**
     * @type {{comments: ?array, load: boolean, user: ?array}}
     */
    state = {
        load        : true,
        comments    : null,
        user        : null
    };

    componentDidUpdate(prevProps, prevState) {
        if(prevProps.animeId !== this.props.animeId || prevProps.episode !== this.props.episode) {
            this.__fetch();
        }
    }

    /**
     * @param {MessageEvent} data
     */
    onEvent(e) {
        const {
            comments
        } = this.state;

        /**
         * @var {{action: string, eventData: object}} data
         */
        const data = JSON.parse(e.data);

        if(data.action === 'add') {
            data.eventData.updated = true;
            this.setState({
                "comments"  : [ data.eventData, ...comments],
            })
        } else if(data.action === 'update') {
            this.setState({
                "comments": comments.map(comment => {
                    if(comment._id.$oid === data.eventData.comment_id) {
                        comment.text = data.eventData.text;
                        comment.updated = true;
                        return comment;
                    } else
                    {
                        return comment;
                    }
                })
            })
        } else if(data.action === 'delete') {
            this.setState({
                "comments": comments.filter(comment => comment._id.$oid !== data.eventData.comment_id)
            });
        }
    }

    /**
     * @var {{animeId: int, episode: int}} props
     */
    componentDidMount() {
        const {
                animeId,
                episode
        } = this.props;
        this.ws = createWs(
            'comments',
            {
                anime_id: animeId, episode
            });
        this.ws.onmessage = this.onEvent.bind(this);

        this.__fetch();
    }

    /**
     *
     */
    componentWillUnmount() {
        this.ws.close();
    }

    /**
     * Отправляет запрос на добавление нового комментария.
     * @param {string} text
     */
    addComment(text) {
        const {
                animeId,
                episode
        } = this.props;

        fetch(
            "video/comments",
            { "anime_id": animeId, episode, text },
            "POST"
        ).then(() => {});
    }

    updateComment(commentId, text) {
        const {
                animeId,
                episode
            } = this.props;

        fetch(
            `video/comments/${commentId}`,
            {"anime_id": animeId, episode, text},
            "POST"
        ).then(() => {});
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
            .then(()=>{});
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

        return <div className="comments">
            {load
                ? <CircularProgress color="inherit" />
                : <CommentsBlock
                    animeId     = { animeId }
                    episode     = { episode }
                    currentUser = { user }
                    comments    = { comments }
                    onSubmit    = { this.addComment.bind(this) }
                    onUpdate    = { this.updateComment.bind(this) }
                    onDelete    = { this.delete.bind(this) }
                />
            }
        </div>;
    }
}

class CommentsBlock extends React.Component {

    state = {
        update: null
    }

    /**
     * @param comment
     */
    onSetUpdate(comment) {
        this.setState({update: comment});
    }

    onSubmit(text) {
        if(this.state.update !== null) {
            this.props.onUpdate(this.state.update._id.$oid, text);
            this.setState({update: null});
        } else
        {
            this.props.onSubmit(text);
        }
    }

    onDelete(commentId) {
        if(this.state.update !== null && this.state.update._id.$oid === commentId) {
            this.setState({update: null});
        }
        this.props.onDelete(commentId);
    }

    onCancelUpdate() {
        this.setState({update: null})
    }

    render() {
        const {
            animeId,
            currentUser,
            comments
        } = this.props,
            {
                update
            } = this.state;

        return <div className="comments__block block">
            <h3>Комментарии</h3>
            {currentUser !== null
                ? <CommentForm
                    animeId         = {animeId}
                    user            = {currentUser}
                    onSubmit        = {this.onSubmit.bind(this)}
                    onCancelUpdate  = {this.onCancelUpdate.bind(this)}
                    updateFor       = {update}
                />
                : null
            }
            { comments.map(comment => {
                return <Comment
                    key         = {comment._id.$oid}
                    comment     = {comment}
                    user        = {currentUser}
                    onDelete    = {this.onDelete.bind(this)}
                    onUpdate    = {this.onSetUpdate.bind(this)}
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
function Comment ({comment, user, onDelete, onUpdate}) {

    /**
     * @var {{
     *      created_at: {$date: {$numberLong: string}},
     *      user: object
     *      text: string
     * }} comment
     */

    const [
        hover,
        setHover
    ] = React.useState(false),
        onMenuClick = action => {
            if(action === 'delete') {
                onDelete(comment._id.$oid);
            } else if(action === 'update') {
                onUpdate(comment);
            }
        };

    return <Paper
        variant      = "outlined"
        className    = {comment.updated ? "comment comment-updated" : 'comment'}
        onMouseEnter = {()=>setHover(true)}
        onMouseLeave = {()=>setHover(false)}
    >
        <Avatar
            className="comment__avatar"
            alt     = {comment.user.nickname}
            src     = {comment.user.avatar}
        />
        <CommentText
            onMenuClick = {onMenuClick}
            showMenu    = {hover && user !== null}
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
                handleClose()
                onClick('update');
            }}>
              <ListItemIcon>
                <EditIcon fontSize="small" />
              </ListItemIcon>
              <ListItemText primary="Изменить" />
            </MenuItem>
            <MenuItem onClick={()=> {
                handleClose()
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

    return <Paper variant="outlined" className="not-found-comment">
        <div className="not-found-comment__content">
            Еще нет комментариев.
            {needAuth
                ? <>
                    <br/>
                    <a href="https://auth.todonime.ru" className="not-found-comment__link">
                        Авторизируйтесь
                    </a> и напишите свой.
                </>
                : null}
        </div>
    </Paper>
}

class CommentText extends React.Component {

    componentDidMount() {
        this.timer = setInterval(this.forceUpdate.bind(this), 60000);
    }

    componentWillUnmount() {
        clearInterval(this.timer);
    }

    render() {
        const {
            user,
            createdAt,
            showMenu,
            onMenuClick,
            text
        } = this.props;

        return <div className="comment__right">
            <div>
            <span className="comment__nickname">
                {user.nickname}<Scopes scopes={user.scope || []}/>
            </span>
                <time className="comment__time">{ moment(createdAt).fromNow() }</time>
                <CommentMenu show={showMenu} onClick={onMenuClick} />
            </div>
            <pre className="comment__text">{ text }</pre>
        </div>
    }
}

function Scopes({scopes}) {
    return <>
        {scopes.map(scope => <Chip
            className="comment__chip"
            color="primary"
            label={scope}
        />)}
    </>
}

class CommentForm extends React.Component {

    inputRef = React.createRef();

    /**
     * @type {{pickerShow: boolean}}
     */
    state = {
        "pickerShow": false,
        "updateFor" : null,
        "value"     : ''
    };

    /**
     * @param prevProps
     * @param prevState
     */
    componentDidUpdate ( prevProps, prevState) {

        if (prevState.pickerShow !== this.state.pickerShow) {

            if (this.state.pickerShow && this.inputRef.current !== document.activeElement) {

                this.inputRef.current.focus();
            }

        }

        if( JSON.stringify(this.props.updateFor) !== JSON.stringify(this.state.updateFor) ) {
                this.setState({updateFor: this.props.updateFor});
        }

        if(JSON.stringify(prevState.updateFor) !== JSON.stringify(this.state.updateFor)) {
            if(this.state.updateFor !== null) {
                this.inputRef.current.focus();
                this.setState({value: this.state.updateFor.text});
            } else
            {
                this.setState({value: ''});
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
        const val = this.state.value;
        const pos = this.inputRef.current.selectionStart;

        this.setState({
            value: val.slice(0, pos) + emoji.native + val.slice(pos + 1)
        });
    }

    onKeyUp(e) {
        if( this.inputRef.current === document.activeElement ) {
            const val = this.state.value.trim();

            if( e.ctrlKey && e.keyCode === 13 ) {
                if( val.length > 0) {
                    this.props.onSubmit(val);
                    this.setState({value: ''})
                }
            }
        }
    }

    onSend() {
        const val = this.state.value.trim();
        if( val.length > 0) {
            this.props.onSubmit(val);
            this.setState({value: ''})
        }
    }

    onChange(e) {
        this.setState({value: e.target.value})
    }

    render () {
        const {
            pickerShow,
            value,
            updateFor
        } = this.state,
            {
                onCancelUpdate
            } = this.props;

        const inputProps = {
            inputProps      : {
                onKeyUp: this.onKeyUp.bind(this)
            },
            startAdornment  : <CommentIcon />,
            endAdornment    : <InputAdornment position="end">
                <IconButton onClick={this.onSend.bind(this)} >
                    <SendIcon color="primary" />
                </IconButton>
                <Emoji
                    show     = {pickerShow}
                    onToggle = {this.togglePicker.bind(this)}
                    onSelect = {this.setEmoji.bind(this)}
                />
            </InputAdornment>
        };

        return <ClickAwayListener
            onClickAway={this.hidePicker.bind(this)}
        >
            <div>
                {updateFor !== null
                    ? <div className="form__header">
                        <span>Редактирование комментария от </span>
                        <time className="form__time">
                            { moment(parseInt(updateFor.created_at.$date.$numberLong)).fromNow() }
                        </time>
                        <IconButton>
                            <CloseIcon onClick={onCancelUpdate} style={{width: "10px", height: "10px"}} />
                        </IconButton>
                    </div>
                    : null
                }
                <TextField
                    inputRef    = {this.inputRef}
                    style       = {{marginBottom: '15px'}}
                    className   = "form"
                    id          = "outlined-multiline-flexible"
                    label       = "Ctrl+Enter - отправить"
                    multiline
                    rowsMax     = {8}
                    onChange    = { this.onChange.bind(this) }
                    variant     = "outlined"
                    InputProps  = {inputProps}
                    value       = {value}
                />
            </div>
        </ClickAwayListener>
    }

}

const CommentIcon = () =>
    <InputAdornment position="start">
        <AddCommentIcon color="primary" />
    </InputAdornment>,

    Emoji = ({show, onToggle, onSelect}) => <span>
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
    </span>;