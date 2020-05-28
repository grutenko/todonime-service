import React from "react";
import EpisodesList from "./EpisodesList";

export default function AnimeInfo ({anime, currentEpisode, lastEpisode, user}) {

    return <EpisodesList
        anime                   = {anime}
        canComplete             = {user !== null}
        lastCompletedEpisode    = {lastEpisode}
        lastEpisode             = {anime.last_episode}
        currentEpisode          = {currentEpisode}
    />

}
