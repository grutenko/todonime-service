import React from "react";

import {fetch} from '../../lib/api';
import ListItemIcon from "@material-ui/core/ListItemIcon";
import Checkbox from "@material-ui/core/Checkbox";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import {withRouter} from "react-router-dom";

class EpisodesList extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            load                : false,
            anime               : props.anime,
            canComplete         : props.canComplete,
            lastCompletedEpisode: props.lastCompletedEpisode,
            lastEpisode         : props.lastEpisode
        }
    }

    componentDidUpdate(prevProps) {
        if(
            prevProps.canComplete !== this.props.canComplete 
            || prevProps.lastCompletedEpisode !== this.props.lastCompletedEpisode
            || prevProps.anime.shikimori_id !== this.props.anime.shikimori_id
            || prevProps.lastEpisode !== this.props.lastEpisode
        )
        {
            this.setState({
                anime               : this.props.anime,
                canComplete         : this.props.canComplete,
                lastCompletedEpisode: this.props.lastCompletedEpisode,
                lastEpisode         : this.props.lastEpisode
            });
        }
    }

    onBumpEpisode(episode) {
        const { canComplete, anime } = this.state,
              { onBumpEpisode } = this.props;

        if(canComplete) {
            fetch(
                "user/episode/watched",
                {
                    "anime_id"  : anime._id.$oid,
                    "episode"   : episode
                },
                "PUT"
            ).then(data => {
                onBumpEpisode(episode);
                this.setState({lastCompletedEpisode: episode});
            });
        }
    }

    renderCheckBox(episodeNumber, labelId) {
        const { lastCompletedEpisode } = this.state;

        return <ListItemIcon>
            <Checkbox
                edge="start"
                checked={lastCompletedEpisode >= episodeNumber}
                tabIndex={-1}
                disableRipple
                inputProps={{ 'aria-labelledby': labelId }}
            />
        </ListItemIcon>
    }

    onClickEpisode(episode) {
        const {
            anime: {
                shikimori_id
            }
        } = this.state;

        return () => this.props.history.push(`/s/${shikimori_id}/${episode}`);
    }

    render() {
        const {
            lastEpisode
        } = this.state;

        return <List
            style={{"flex": 1}}
            component="div"
            aria-label="main mailbox folders"
        >
            {Array.from(Array(lastEpisode).keys()).map(episode =>
                <ListItem key={episode + 1} dense button>
                    <div onClick={this.onClickEpisode(episode + 1)} style={{width: '100%', "padding": '10px'}}>
                        {episode + 1}
                    </div>
                </ListItem>
            )}
        </List>
    }
}

export default withRouter(EpisodesList);
