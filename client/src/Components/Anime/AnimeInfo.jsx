import React from "react";
import EpisodesList from "./EpisodesList";

export default function AnimeInfo ({anime, user}) {

    return <EpisodesList
        anime                   = {anime}
        canComplete             = {user !== null}
        lastCompletedEpisode    = {5}
        lastEpisode             = {anime.last_episode}
    />

}
