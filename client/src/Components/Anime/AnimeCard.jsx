import React from "react";
import Card from "@material-ui/core/Card";
import CardMedia from "@material-ui/core/CardMedia";
import Typography from "@material-ui/core/Typography";
import LinearProgress from "@material-ui/core/LinearProgress";
import Button from "@material-ui/core/Button";

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
                <Typography component="h5" variant="h5">
                    {anime.name_ru || anime.name_en}
                </Typography>
                <Typography variant="subtitle1" color="textSecondary">
                    {lastEpisode} / {anime.last_episode}
                </Typography>
                <Button
                    variant ="outlined"
                    color   ="primary"
                    href    ={"https://shikimori.one" + anime.url}
                >
                    Подробнее на shikimori
                </Button>
            </div>
            <LinearProgress
                variant="determinate"
                value={(lastEpisode / anime.last_episode) * 100}
            />
        </div>
    </Card>
}
