import React from "react";

export default function AnimeInfo ({anime}) {

    return <>
        <img src={
            (process.env.REACT_APP_ENV === "local"
                ? "http://cdn.todonime.lc"
                : "https://cdn.todonime.ru") +
            anime.poster.original}
        />
    </>;

}
