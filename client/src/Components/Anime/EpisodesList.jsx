import React from "react";

import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import {withRouter} from "react-router-dom";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import Checkbox from "@material-ui/core/Checkbox";

import {fetch} from '../../lib/api';

class EpisodesList extends React.Component {

    styles = {
        "list": {
            "flex": 1
        },
        "item": {
            "width": '100%',
            "padding": '10px'
        }
    }

    constructor(props) {
        super(props);

        this.state = {
            currentEpisode      : props.currentEpisode,
            lastCompletedEpisode: props.lastCompletedEpisode
        };
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
        }
    }

    onBumpEpisode(episode) {
        return () => {
            if (this.props.canComplete) {
                if(this.state.lastCompletedEpisode >= episode) {
                    episode--;
                }

                fetch(
                    "user/episode/watched",
                    {
                        "anime_id": this.props.anime._id.$oid,
                        "episode": episode
                    },
                    "PUT"
                ).then((data) => {

                    this.setState({lastCompletedEpisode: episode})

                });

            }
        }
    }

    checkBox(checked, episode) {
        return <ListItemIcon>
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

    render() {
        const {
            lastEpisode
        } = this.props,
            {
                currentEpisode,
                lastCompletedEpisode
            } = this.state;

        return <List
            style={{"flex": 1}}
            component="div"
            aria-label="main mailbox folders"
        >
            {Array.from(Array(lastEpisode).keys()).map(episode => {
                episode = episode + 1;

                return <ListItem
                    key     ={episode}
                    selected={episode === currentEpisode}
                    dense
                    button
                >
                    { this.checkBox( lastCompletedEpisode >= episode, episode)}
                    <div
                        onClick={this.onClickEpisode(episode)}
                        style={this.styles.item}
                    >
                        {episode}
                    </div>
                </ListItem>
            })}
        </List>
    }
}

export default withRouter(EpisodesList);
