import React from "react";
import Card from "@material-ui/core/Card";
import CardMedia from "@material-ui/core/CardMedia";
import Typography from "@material-ui/core/Typography";
import LinearProgress from "@material-ui/core/LinearProgress";
import Button from "@material-ui/core/Button";
import {Star, StarBorder, StarHalf} from "@material-ui/icons";

export default function AnimeCard ({anime, currentEpisode, lastEpisode, user}) {

    const styles = {
        root: {
            display: "flex"
        },
        cover: {
            width: "151px"
        },
        content: {
            flex: '1 0 auto',
        },
        text: {
            margin: "10px 15px 15px 15px",
        },
        progress: {
            margin: '0'
        },
        progressText: {
            fontSize: '12px',
            color: '#898989',
            margin: '0 15px'
        }
    };

    return <Card style={styles.root}>
        <CardMedia
            style       = {styles.cover}
            image       = {process.env.REACT_APP_CDN_BASE + anime.poster.original}
            title       = {anime.name_ru || anime.name_en}
        />
        <div style={styles.content}>
            <div style={styles.text}>
                <a href = {"https://shikimori.one" + anime.url}>
                    <Typography component="h5" variant="h5">
                        {(anime.name_ru  ? anime.name_ru + ' / ': '') + anime.name_en}
                    </Typography>
                </a>
                <Rating rating={anime.rating} />
            </div>
            <span style={styles.progressText}>Текущий прогресс: {lastEpisode} / {anime.last_episode}</span>
            <LinearProgress
                style={styles.progress}
                variant="determinate"
                value={(lastEpisode / anime.last_episode) * 100}
            />
        </div>
    </Card>
}

const Rating = ({rating}) =>
    <div style={{display: 'flex'}}>
        {Array.from(Array(5).keys()).map((i) =>
            i+1 <= Math.floor(rating/2)
                ? <Star color="primary"/>
                : rating/2 - Math.floor(rating/2) > 0.25
                    ? <StarBorder color="primary"/>
                    : <StarHalf color="primary" />
        )}
        <span style={{margin: 'auto 15px'}}>{rating}</span>
    </div>
