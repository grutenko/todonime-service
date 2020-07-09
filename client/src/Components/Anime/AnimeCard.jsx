import React from "react";
import Card from "@material-ui/core/Card";
import CardMedia from "@material-ui/core/CardMedia";
import Typography from "@material-ui/core/Typography";
import LinearProgress from "@material-ui/core/LinearProgress";
import {Star, StarBorder} from "@material-ui/icons";
import {Link} from "react-router-dom";

import './AnimeCard.css'

export default function AnimeCard ({anime, currentEpisode, lastEpisode, user, toWatch}) {
    return <Card className="anime-card">
        <CardMedia
            className   = "anime-card__cover"
            image       = {process.env.REACT_APP_CDN_BASE + anime.poster.original}
            title       = {anime.name_ru || anime.name_en}
        />
        <div className="anime-card__content">
            <div className="anime-card__text">
                {toWatch
                    ? <Link to={`/s/${anime.shikimori_id}/${lastEpisode + 1}`}>
                        <Typography component="div" variant="div" className="anime-card__text-header">
                            {anime.name_ru  ? anime.name_ru : anime.name_en}
                        </Typography>
                    </Link>
                    : <a href = { "https://shikimori.one" + anime.url}>
                        <Typography component="div" variant="div" className="anime-card__text-header">
                            {anime.name_ru  ? anime.name_ru : anime.name_en}
                        </Typography>
                    </a>
                }
                <Rating rating={anime.rating} />
            </div>
            <span className="anime-card__progress-text">Текущий прогресс: {lastEpisode} / {anime.last_episode}</span>
            <LinearProgress
                className="anime-card__progress"
                variant="determinate"
                value={(lastEpisode / anime.last_episode) * 100}
            />
        </div>
    </Card>
}

const Rating = ({rating}) =>
    <div style={{display: 'flex'}}>
        {Array.from(Array(5).keys()).map((i) =>
            i + 1 <= Math.ceil(rating / 2) ? <Star color="primary"/> : <StarBorder color="primary"/>
        )}
        <span style={{margin: 'auto 15px'}}>{rating}</span>
    </div>
