import React from "react";

import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import {withRouter} from "react-router-dom";

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
            currentEpisode: props.currentEpisode
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

    render() {
        const {
            lastEpisode,
        } = this.props,
            {
                currentEpisode
            } = this.state;

        return <List
            style={{"flex": 1}}
            component="div"
            aria-label="main mailbox folders"
        >
            {Array.from(Array(lastEpisode).keys()).map(episode =>
                <ListItem
                    key={episode + 1}
                    selected={episode + 1 === currentEpisode}
                    dense
                    button
                >
                    <div
                        onClick={this.onClickEpisode(episode + 1)}
                        style={this.styles.item}
                    >
                        {episode + 1}
                    </div>
                </ListItem>
            )}
        </List>
    }
}

export default withRouter(EpisodesList);
