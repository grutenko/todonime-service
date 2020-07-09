import React from "react";

import AnimeCard from './AnimeCard';
import { fetch } from '../../lib/api'
import Loader from '../Misc/Loader'
import { SignalCellularNull } from "@material-ui/icons";

import './WatchList.css';

export default class WatchList extends React.Component {
 
    constructor(props) {
        super(props);
        this.state = {
            animes: null,
            user: null
        };
    }

    componentDidMount() {
        var animes;

        fetch("user/watchlist", {limit: 50})
            .then(data => {
                animes = data.data;
                return fetch("user/current");
            })
            .then(data => {
                this.setState({
                    animes,
                    user: data.data
                });
            })
    }

    render() {
        const {
            animes,
            user
        } = this.state;

        return animes
            ? <div class="watch-list__container">
                <div class="watch-list">
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
            </div>
            : <Loader/>
    }
}