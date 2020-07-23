import React from 'react';
import { cdn } from '../../lib/path';

import './TodonimePlayerIframe.css';
import './InternalPlayer.css';
import Slider from '@material-ui/core/Slider';
import PlayArrowIcon from '@material-ui/icons/PlayArrow';
import FullscreenIcon from '@material-ui/icons/Fullscreen';
import FullscreenExitIcon from '@material-ui/icons/FullscreenExit';
import VolumeUpIcon from '@material-ui/icons/VolumeUp';
import VolumeDownIcon from '@material-ui/icons/VolumeDown';
import VolumeMuteIcon from '@material-ui/icons/VolumeMute';
import VolumeOffIcon from '@material-ui/icons/VolumeOff';
import PauseIcon from '@material-ui/icons/Pause';
import FastForwardIcon from '@material-ui/icons/FastForward';
import FastRewindIcon from '@material-ui/icons/FastRewind';
import CircularProgress from '@material-ui/core/CircularProgress';
import AnnouncementIcon from '@material-ui/icons/Announcement';
import Button from '@material-ui/core/Button';

import libjass from 'libjass';
import moment from 'moment';
import { IconButton } from '@material-ui/core';
import { CollectionsOutlined } from '@material-ui/icons';

export default class InternalPlayer extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            ready: false,
            fullscreen: false,
            played: false,
            loaded: false,
            width: null,
            height: null,
            progress: 0,
            volume: 100,
            off: false,
            showSkipButton: false,
            skipType: 'op',
            statusIcon: null,
            statusValue: null
        }
        this.ref = React.createRef();
        this.videoRef = React.createRef();

        this.onResize = this.onResize.bind(this);
        this.onTogglePlay = this.onTogglePlay.bind(this);
        this.setProgress = this.setProgress.bind(this);
        this.onChangeProgress = this.onChangeProgress.bind(this);
        this.setHover = this.setHover.bind(this);
        this.unsetHover = this.unsetHover.bind(this);
        this.fullscreenSetHover = this.fullscreenSetHover.bind(this);
        this.toggleFullscreen = this.toggleFullscreen.bind(this);
        this.onCanplay = this.onCanplay.bind(this);
        this.onWaiting = this.onWaiting.bind(this);
        this.skip = this.skip.bind(this);

        this.unhoverTimeout = null;
        this.hideTimeout = null;
    }

    componentDidMount() {
        this.onResize();
        this.setProgressHandler = setInterval(this.setProgress, 250);
        window.addEventListener('resize', this.onResize);
        this.ref.current.addEventListener('keyup', this.onKey, {passive: false});
        this.videoRef.current.onwaiting = this.onWaiting;
        this.videoRef.current.oncanplay = this.onCanplay;
        this.videoRef.current.onloadedmetadata = () => {
            this.setState({ready: true});
        }

    }

    componentWillUnmount() {
        clearInterval(this.setProgressHandler);
        window.removeEventListener('resize', this.onResize);
        this.ref.current.removeEventListener('keyup', this.onKey);
        this.videoRef.current.onwaiting = ()=>{};
        this.videoRef.current.oncanplay = ()=>{};
        this.videoRef.current.onloadedmetadata = ()=>{}
    }

    componentDidUpdate(prevProps, prevState) {
        if(prevProps.binary !== this.props.binary
            || prevProps.subtitles !== this.props.subtitles) {
                this.forceUpdate();
        }

        if(this.state.ready) {
            const currentTime = this.videoRef.current.currentTime;
            if(this.props.binary.op
                && this.props.binary.op.start
                && this.props.binary.op.end
            ) {
                if(currentTime >= this.props.binary.op.start
                    && currentTime <= this.props.binary.op.end
                ) {
                    if(!this.state.showSkipButton || this.state.skipType !== 'op') {
                        this.setState({showSkipButton: true, skipType: 'op'});
                    }
                } else if(currentTime >= this.props.binary.ed.start
                    && currentTime <= this.props.binary.ed.end
                ) {
                    if(!this.state.showSkipButton || this.state.skipType !== 'ed') {
                        this.setState({showSkipButton: true, skipType: 'ed'});
                    }
                } else {
                    if(this.state.showSkipButton) {
                        this.setState({showSkipButton: false, skipType: 'op'});
                    }
                }
            }
        }

        if(prevState.played !== this.state.played) {
            this.togglePlay();
        }

        if(prevState.fullscreen !== this.state.fullscreen) {
            if (this.state.fullscreen) {
                const el = this.ref.current;
                if (el.requestFullscreen) {
                    el.requestFullscreen();
                } else if (el.mozRequestFullScreen) {
                    el.mozRequestFullScreen();
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                } else if (el.msRequestFullscreen) {
                    el.msRequestFullscreen();
                }

                document.addEventListener('fullscreenchange', () => {
                    this.setState({width: window.screen.width, height: window.screen.height});
                }, {once: true});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }

                document.addEventListener('fullscreenchange', this.onResize, {once: true});
            }
        }

        if(prevState.off !== this.state.off) {
            this.videoRef.current.muted = this.state.off;
            this.setState({
                statusIcon: this.state.off ? 'volumeMuted' : 'volumeUp',
                statusValue: null
            });
        }

        if(prevState.volume !== this.state.volume) {
            this.videoRef.current.volume = this.state.volume / 100;
            this.setState({
                statusIcon: this.state.volume > prevState.volume ? 'volumeUp' : 'volumeDown',
                statusValue: Math.floor(this.state.volume) + '%'
            });
        }

        if(prevState.statusIcon !== this.state.statusIcon && this.state.statusIcon != null) {
            clearTimeout(this.hideTimeout);
            this.hideTimeout = setTimeout(() => this.setState({statusIcon: null, statusValue: null}), 800);
        }

        if(prevState.opSelected !== this.state.opSelected) {
            const duration = this.videoRef.current.currentTime;
            this.setState({
                statusIcon: 'alert',
                statusValue: (this.state.opSelected ? 'OP START ' : 'OP END ') 
                    + moment.utc(duration * 1000).format(duration < 3600 ? 'mm:ss' : 'H:mm:ss')
            });
        }
    }

    getMarks() {
        const {
            binary
        } = this.props;

        var marks = [];
        if(binary.op && binary.op.start && binary.op.end) {
            marks.push({
                value: binary.op.start / this.videoRef.current.duration * 100,
                label: 'Опенинг'
            });
            marks.push({
                value:  binary.op.end / this.videoRef.current.duration * 100
            });
        }
        if(binary.ed && binary.ed.start && binary.ed.end) {
            marks.push({
                value: binary.ed.start / this.videoRef.current.duration * 100,
                label: 'Эндинг'
            });
            marks.push({
                value:  binary.ed.end / this.videoRef.current.duration * 100
            });
        }

        return marks;
    }

    setProgress() {
        const video = this.videoRef.current;
        this.setState({progress:  video.currentTime /  video.duration * 100});
    }

    onResize(e) {
        this.setState({
            width: this.ref.current.clientWidth,
            height: this.ref.current.clientHeight
        });
    }

    onTogglePlay(e) {
        if(e === undefined || e.target.closest('.video__toolbar-item') === null) {
            this.setState({played: !this.state.played});
        }
    }

    togglePlay() {
        if(this.state.played)
            this.videoRef.current.play();
        else
            this.videoRef.current.pause();
    }

    onChangeProgress(e, v) {
        const video = this.videoRef.current;
        const lastTime =  video.currentTime;
        video.currentTime = video.duration * (v / 100);

        const distance = Math.floor(Math.abs(video.duration * (v / 100) - lastTime));
        if(distance > 0) {
            this.setState({
                statusIcon: video.currentTime > lastTime ? 'forward' : 'rewind',
                statusValue: moment.utc(video.duration * (v / 100) * 1000)
                                   .format(video.duration * (v / 100) < 3600 ? 'mm:ss' : 'H:mm:ss')
            });
        }
    }

    setHover() {
        this.setState({hover: true});
    }

    unsetHover() {
        this.setState({hover: false})
    }

    fullscreenSetHover() {
        if(this.state.fullscreen) {
            this.setHover();
            if(this.unhoverTimeout) {
                clearTimeout(this.unhoverTimeout);
            }
            this.unhoverTimeout = setTimeout(this.unsetHover, 3000);
        }
    }

    toggleFullscreen() {
        this.setState({fullscreen: !this.state.fullscreen})
    }

    onWaiting() {
        this.setState({loaded: false});
    }

    onCanplay() {
        this.setState({loaded: true});
    }

    skip() {
        if(this.state.skipType == 'op') {
            this.onChangeProgress(undefined, this.props.binary.op.end / this.videoRef.current.duration * 100);
        } else {
            this.onChangeProgress(undefined, this.props.binary.ed.end / this.videoRef.current.duration * 100);
        }
    }

    render() {
        const {
            binary,
            sub
        } = this.props;
        const {
            width,
            height,
            played,
            loaded,
            progress,
            fullscreen,
            volume,
            off,
            hover,
            statusIcon,
            statusValue,
            ready,
            showSkipButton,
            skipType
        } = this.state;

        return <div
            ref         = {this.ref}
            className   = {"video-player__iframe internal" + (hover ? " hover" : "")}
            onClick     = {this.onTogglePlay}
            onMouseOver = {this.setHover}
            onMouseLeave= {this.unsetHover}
            onMouseMove = {this.fullscreenSetHover}
        >
            <div class={"video__toolbar" + (played ? " play" : "")} style={{width, height}}>
                {statusIcon
                    ? <InfoBar width={width} height={height / 2}>
                        <span>{statusValue}</span>
                        {{
                            'volumeUp'  : <VolumeUpIcon style={{ color: '#ffffff', fontSize: '60px' }}/>,
                            'volumeDown': <VolumeDownIcon style={{ color: '#ffffff', fontSize: '60px' }}/>,
                            'volumeMuted': <VolumeOffIcon style={{ color: '#ffffff', fontSize: '60px' }}/>,
                            'forward': <FastForwardIcon style={{ color: '#ffffff', fontSize: '60px' }}/>,
                            'rewind': <FastRewindIcon style={{ color: '#ffffff', fontSize: '60px' }}/>
                        }[statusIcon] || <AnnouncementIcon style={{ color: '#ffffff', fontSize: '60px' }}/>}
                    </InfoBar>
                    : null
                }
                {ready
                    ? <Subtitles
                        video       = {this.videoRef.current}
                        subtitles   = {sub.data}
                        width       = {width}
                        height      = {height}
                        fontSize    = {35}
                    />
                    : null
                }
                <div style={{flex: 1, display: 'flex', width: "100%"}}>
                    <PlayButton show={!played} />
                    <Loader show={!loaded && played}/>
                </div>
                {ready
                    ?  <Toolbar>
                        <IconButton onClick={() => this.onTogglePlay(undefined)}>
                        {played
                            ? <PauseIcon style={{ color: '#ffffff' }} />
                            : <PlayArrowIcon  style={{ color: '#ffffff' }} />
                        }
                        </IconButton>
                        <CurrentTime
                            currentTime={this.videoRef.current ? this.videoRef.current.currentTime : 0}
                            duration={this.videoRef.duration ? this.videoRef.current.duration : 0}
                        />
                        <Slider
                            value={progress}
                            onChange={this.onChangeProgress}
                            style={{flex: 1}}
                            marks={this.getMarks()}
                        />
                        <Volume
                            value = {volume}
                            off = {off}
                            onChange = {(e, v) => this.setState({volume: v})}
                            onOff = {() => this.setState({off: !this.state.off})}
                        />
                        <SkipButton show={showSkipButton} type={skipType} onClick={this.skip}/>
                        <IconButton onClick={this.toggleFullscreen}>
                        {fullscreen
                            ? <FullscreenExitIcon style={{ color: '#ffffff' }} />
                            : <FullscreenIcon style={{ color: '#ffffff' }} />
                        }
                        </IconButton>
                    </Toolbar>
                    : null
                }
            </div>
            <video
                ref         = {this.videoRef}
                className   = "internal__video"
                autoBuffer  = "true"
                crossOrigin = "anonymous"
                poster      = {cdn(binary.preview)}
            >
                <source src={cdn(binary.video)} type='video/mp4'/>
            </video>
        </div>
    }
}

function SkipButton({show, type, onClick}) {
    return <Button className="skip-button" onClick={onClick} style={!show ? {display: 'none'} : {}}>
        {type == 'op' ? 'Пропустить опенинг' : 'Пропустить эндинг'}
    </Button>
}

function InfoBar({width, height, children}) {
    return <div
        className="info__bar"
        style={{width, height}}
    >
            <div className="info__bar-content">{children}</div>
    </div>
}

class Volume extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            hover: false
        }
        this.ref = React.createRef();

        this.onWheelChange = this.onWheelChange.bind(this);
    }

    componentDidMount() {
        this.ref.current.addEventListener('mousewheel', this.onWheelChange, { passive: false });
    }

    componentWillUnmount() {
        this.ref.current.removeEventListener('mousewheel', this.onWheelChange);
    }

    onWheelChange(e) {
        const {value} = this.props;

        e.preventDefault();
        const d = e.deltaY || e.detail || e.wheelDelta;
        if((d > 0 && value < 100) || (d < 0 && value > 0)) {
            const newValue = value + d * 0.5;
            this.props.onChange(e, newValue <= 100 ? newValue >= 0 ? newValue : 0 : 100 )
        }
    }

    getIcon() {
        const {
            value,
            off
        } = this.props;

        if(!off) {
            if(value >= 50) {
                return <VolumeUpIcon style={{color: '#fff'}} />
            } else if(value < 50 && value >= 20) {
               return <VolumeDownIcon style={{color: '#fff'}} />
            } else {
                return <VolumeMuteIcon style={{color: '#fff'}} />
            }
        } else {
            return <VolumeOffIcon style={{color: '#fff'}} />
        }
    }

    render() {
        const {value, onOff, onChange} = this.props;
        const {hover} = this.state;

        return <span
            style={{marginTop: '-10px'}}
            ref={this.ref}
            onMouseOver = {() => this.setState({hover: true})}
            onMouseLeave = {() => this.setState({hover: false})}
        >
            <Slider
                orientation="vertical"
                value={value}
                onChange={onChange}
                style={{
                    height: "100px",
                    position: 'absolute',
                    right: '60px',
                    bottom: '60px',
                    opacity: hover ? 1 : 0
                }}
            />
            <IconButton onClick={onOff}>{this.getIcon()}</IconButton>
        </span>
    }
}

class Subtitles extends React.Component {
    ref = React.createRef();

    componentDidMount() {
        const  {
            subtitles,
            video
        } = this.props;

        libjass.ASS.fromString(subtitles).then(ass =>
            new libjass.renderers.WebRenderer(
                ass,
                new libjass.renderers.VideoClock(video),
                this.ref.current
            ));
    }

    componentDidUpdate(prevProps) {
        if(prevProps.width !== this.props.width
            || prevProps.width !== this.props.width
            || prevProps.fontSize !== this.props.fontSize) {
                this.forceUpdate();
        }
    }

    render() {
        const {
            width,
            height,
            fontSize
        } = this.props;

        return <div ref={this.ref} class="video__subtitles" style={{width, height, fontSize}}></div>
    }
}

const CurrentTime = ({currentTime, duration}) => 
    <span style={{marginTop: "3px", marginRight: "15px"}}>
        {moment.utc(currentTime * 1000).format(duration < 3600 ? 'mm:ss' : 'H:mm:ss')}
    </span>
const Toolbar = ({children}) => <div className="video__toolbar-item">{children}</div>

const Loader = ({show}) => <CircularProgress
    className="internal__loader"
    style={{color: "white",display: show ? 'block' : 'none'}}
/>
const PlayButton = ({show}) => <PlayArrowIcon
    className="play__button"
    style={{
        color: '#ffffff',
        fontSize: 60,
        display: show ? 'block' : 'none'
    }}
/>