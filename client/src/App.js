import React, {useState} from "react";
/* eslint-disable */
import Layout from "./Components/Layout";
import {Route, Switch} from "react-router-dom";
import VideoPlayer from "./Components/Video/VideoPlayer";
import Menu from './Components/Menu';

import * as Api from './lib/api';
/* eslint-enable */

export default function App () {

    const [
            content,
            setMenuContent
        ] = useState(null),

        [
            title,
            setTitleContent
        ] = useState("Todonime"),

        setMenu = (dom) => {

            setMenuContent(dom);

        },
        setTitle = (siteTitle, dom) => {
            document.getElementsByTagName('title')[0].text = siteTitle;
            setTitleContent(dom);
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

class SuggestVideo extends React.Component {

    state = {
        "load": false,
        "notFound": false,
    };

    fetch() {
        const {
            match: {
                params: {
                    animeId,
                    episode
                }
            },
            history
        } = this.props;

        Api.fetch(
            "video/suggest",
            {
                "anime_id": animeId,
                episode
            }
        ).then((data) => {

            this.setState({
                load: false
            })
            history.replace(`/s/${animeId}/${episode}`);
            history.push(`/v/${data.data.video_id}`);

        }).catch( () => {

            this.setState({
                load: true,
                notFound: true
            });

        })
    }

    componentDidMount() {
        this.fetch();
    }

    componentDidUpdate(prevProps) {
        if(prevProps.animeId !== this.props.animeId || prevProps.episode !== this.props.episode) {

            this.fetch();

        }
    }

    render() {
        return <div/>
    }
}