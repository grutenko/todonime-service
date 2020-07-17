import React from "react";

import AnimeCard from './AnimeCard';
import { fetch } from '../../lib/api'
import CircularProgress from "@material-ui/core/CircularProgress";

import './WatchList.css';

export default class WatchList extends React.Component {
 
    constructor(props) {
        super(props);
        this.state = {
            load: true,
            animes: null,
            user: null
        };
    }

    componentDidMount() {
        var animes;

        fetch("user/watchlist", {limit: 50})
            .then(data => {
                this.setState({animes: data.data})
                return fetch("user/current");
            }, (err) => this.setState({load: false}))
            .then(data => {
                this.setState({
                    load: false,
                    user: data.data.user
                });
            })
    }

    renderList() {
        const {
            animes,
            user
        } = this.state;

        if(!user || !animes)
        {
            return <div className="watch-list"></div>
        }

        return <div className="watch-list">
            {animes.map((item, i) => 
                <div style={{marginTop: '15px'}}>
                    <AnimeCard
                        anime={item}
                        currentEpisode={0}
                        lastEpisode={item.watched || 0}
                        user={user}
                        toWatch={true}
                    />
                </div>
            )}
        </div>
    }

    render() {
        const {
            load
        } = this.state;

        return !load
            ? <div class="watch-list__container">
                {this.renderList()}
            </div>
            : <CircularProgress
                color="primary"
                style={{margin: "20px"}}
            />
    }
}