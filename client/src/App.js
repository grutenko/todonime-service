import React, {useState} from "react";
/* eslint-disable */
import Layout from "./Components/Layout";
import {Route, Switch} from "react-router-dom";
import VideoPlayer from "./Components/Video/VideoPlayer";
import Menu from './Components/Menu';
import {withRouter} from "react-router-dom";

import * as Api from './lib/api';
import Loader from "./Components/Misc/Loader";
/* eslint-enable */

export default function App () {

    const [
            content,
            setMenuContent
        ] = useState(null),

        [
            title,
            setTitle
        ] = useState("Todonime"),

        setMenu = (dom) => {

            setMenuContent(dom);

        };

    return (
        <div className="App">
            {/* eslint-disable-next-line max-statements-per-line,max-len */}
            <Layout title={title} setMenu={setMenu} menuOpen={content != null}>
                <Switch>
                    {/* eslint-disable-next-line max-len */}
                    <Route exact path="/v/:id" render={(props) => <VideoPlayer
                        setTitle={setTitle}
                        setMenu={setMenu}
                        menuOpen={content != null}
                        {...props}
                    />
                    }
                    />
                    <Route
                        exact
                        path="/s/:animeId/:episode"
                        component={SuggestVideo}
                    />
                    <Route
                        exact
                        path="/video/:animeId/:episode"
                        component={SuggestVideo}
                    />
                </Switch>
            </Layout>
            {/* eslint-disable-next-line no-eq-null */}
            <Menu isShow={content != null} onClose={() => setMenuContent(null)}>
                {content}
            </Menu>
        </div>
    );

}

function SuggestVideo ({history, "match": {"params": {animeId, episode}}}) {

    const [
        load,
        setLoad
    ] = React.useState(false);

    if (!load) {

        Api.fetch(
            "video/suggest",
            {"anime_id": animeId,
                episode}
        ).then((data) => {

            setLoad(true);
            history.replace(`/s/${animeId}/${episode}`);
            history.push(`/v/${data.data.video_id}`);
        });

    }

    return <Loader/>;

}

SuggestVideo = withRouter(SuggestVideo);
