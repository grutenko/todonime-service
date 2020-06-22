import React from "react";

import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import {withRouter} from "react-router-dom";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import Checkbox from "@material-ui/core/Checkbox";

import {fetch} from '../../lib/api';
import {RollbackEpisodeDialog} from "../Video/VideoPlayer";
import Typography from "@material-ui/core/Typography";
import Tooltip from "@material-ui/core/Tooltip";

import './EpisodesList.css';

class EpisodesList extends React.Component {

    constructor(props) {
        super(props);

        const episodesShow = props.lastEpisode <= 50
            ? props.lastEpisode
            : props.currentEpisode > 50
                ? props.currentEpisode
                : 50

        this.state = {
            episodesShow,
            currentEpisode      : props.currentEpisode,
            lastCompletedEpisode: props.lastCompletedEpisode,
            showRollbackEpisodeDialog: false
        };

        this.onScrollMenu = this.onScrollMenu.bind(this);

        this.currentRef = React.createRef();
    }

    onScrollMenu(e) {
        if( this.state.episodesShow >= this.props.lastEpisode) {
            return;
        }

        const menu = document
            .getElementsByClassName('menu-scrollable')
            [0];

        if(menu.scrollTop >= menu.scrollHeight - 1500) {
            this.setState({
                episodesShow: this.state.episodesShow + 50 > this.props.lastEpisode
                    ? this.props.lastEpisode
                    : this.state.episodesShow + 50
            })
        }
    }

    componentDidMount() {
        let menu = document
            .getElementsByClassName('menu-scrollable')
            [0];

        menu.addEventListener('scroll', this.onScrollMenu);
        menu.scrollTo(0, this.currentRef.current.offsetTop);
    }

    componentWillUnmount() {
        document
            .getElementsByClassName('menu-scrollable')
            [0]
            .removeEventListener('scroll', this.onScrollMenu);
    }

    componentDidUpdate(prevProps) {
        if(prevProps.currentEpisode !== this.props.currentEpisode) {

            this.setState({currentEpisode: this.props.currentEpisode});

        }
    }

    onClickEpisode(episode) {
        const {
            anime: {
                shikimori_id
            }
        } = this.props;

        return () => {
            this.setState({currentEpisode: episode})
            this.props.history.push(`/s/${shikimori_id}/${episode}`);
            this.props.onClick();
        }
    }

    confirmBumpEpisode(episode) {
        const {
            rollbackEpisode
        } = this.state;

        if (!this.props.canComplete) {
            return;
        }

        episode = typeof episode === 'number' ? episode : rollbackEpisode;

        fetch(
            "user/episode/watched",
            {
                "anime_id": this.props.anime._id.$oid,
                "episode": episode
            },
            "PUT"
        ).then((data) => {
            this.setState({
                lastCompletedEpisode: episode,
                showRollbackEpisodeDialog: false,
                rollbackEpisode: -1
            })
        });
    }

    onBumpEpisode(episode) {
        return () => {
            if(this.state.lastCompletedEpisode < episode) {
                this.confirmBumpEpisode(episode);
            } else
            {
                this.setState({
                    showRollbackEpisodeDialog: true,
                    rollbackEpisode: episode - 1
                });
            }
        }
    }

    checkBox(checked, episode) {
        return <ListItemIcon style={{minWidth: 0}}>
            <Checkbox
                color           = "primary"
                edge            = "start"
                checked         = {checked}
                tabIndex        = {-1}
                onClick         = {this.onBumpEpisode(episode)}
                disableRipple
            />
        </ListItemIcon>
    }

    getFillersForArch(start, end) {
        return Object.entries(this.props.anime.episodes || {})
            .filter(([i, item]) => i >= start && i <= end && item.type === 'filler');
    }

    renderArchs() {
        const {anime} = this.props,
            {
                lastCompletedEpisode,
                episodesShow
            } = this.state,
            arches = anime.arches
                .sort((i, j) => i.start - j.start)
                .filter(i => i.start < episodesShow);

        return <div className="arches__root">
            {arches.map((arch, i) =>
                <div className="arch" key={i} style={{height: (episodesShow >= arch.end
                        ? (59.2 * (arch.end - arch.start + 1) - 1)
                        : episodesShow) + 'px'}}>
                    {this.getFillersForArch(arch.start, episodesShow >= arch.end ? arch.end : episodesShow).map(([i, item]) =>
                        <div style={{marginTop: (59.2 * (i - arch.start)) + 'px'}} className="arch__filler" title="Филлерный эпизод"></div>
                    )}
                    {lastCompletedEpisode - arch.start >= 0
                        ? <div
                            className="arch__progress"
                            style={{
                                height: lastCompletedEpisode >= (episodesShow >= arch.end ? arch.end : episodesShow)
                                    ? (59.2 * ((episodesShow >= arch.end ? arch.end : episodesShow) - arch.start + 1) - 1) + 'px'
                                    : (59.2 * (lastCompletedEpisode - arch.start + 1) - 1) + 'px',
                            }}>
                            {lastCompletedEpisode < arch.end
                                ? <span className="arch__progress-percent">
                                    {Math.floor((lastCompletedEpisode - arch.start + 1) / ((episodesShow >= arch.end ? arch.end : episodesShow) - arch.start + 1) * 100)}%
                                </span>
                                : null
                            }
                        </div>
                        : null
                    }
                    <div className="arch__text" title={arch.name}>{arch.name}</div>
                </div>
            )}
        </div>
    }

    render() {
        const {
            canComplete
        } = this.props,
            {
                episodesShow,
                currentEpisode,
                rollbackEpisode,
                lastCompletedEpisode,
                showRollbackEpisodeDialog
            } = this.state;

        return <>
            <RollbackEpisodeDialog
                completed   = {lastCompletedEpisode}
                current     = {rollbackEpisode}
                open        = {showRollbackEpisodeDialog}
                onClose     = {()=>this.setState({showRollbackEpisodeDialog: false})}
                onRollback  = {this.confirmBumpEpisode.bind(this)}
            />
            <div style={{display: 'flex'}}>
                <List
                    style={{"flex": 1}}
                    component="div"
                    aria-label="main mailbox folders"
                >
                    {Array.from(Array(episodesShow).keys()).map(episode => {
                        episode = episode + 1;

                        return <ListItem
                            ref={episode === currentEpisode ? this.currentRef : React.createRef()}
                            key     = {episode}
                            selected= {episode === currentEpisode}
                            dense
                            button
                        >
                            {canComplete
                                ? this.checkBox( lastCompletedEpisode >= episode, episode)
                                : null}
                            <div
                                onClick={this.onClickEpisode(episode)}
                                className="episodes__item"
                            >
                                <span className="episodes__number">{episode}</span>
                                <Tooltip title={this.props.anime.episodes && this.props.anime.episodes[ episode ]
                                    ? this.props.anime.episodes[ episode ]['name'] || ''
                                    : ''
                                }>
                                    <Typography
                                        className="episode__name"
                                        display = "inline"
                                        variant = "overline"
                                        noWrap  = "true"
                                    >
                                        {this.props.anime.episodes && this.props.anime.episodes[ episode ]
                                            ? this.props.anime.episodes[ episode ]['name'] || ''
                                            : ''
                                        }
                                    </Typography>
                                </Tooltip>
                            </div>
                        </ListItem>
                    })}
                </List>
                { this.props.anime.arches
                    ? this.renderArchs()
                    : null
                }
            </div>
        </>
    }
}

export default withRouter(EpisodesList);
