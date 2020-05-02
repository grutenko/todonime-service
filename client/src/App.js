import React, {useState} from "react";
/* eslint-disable */
import Layout from "./Components/Layout";
import {Route, Switch} from "react-router-dom";
import VideoPlayer from "./Components/Video/VideoPlayer";
import Menu from './Components/Menu';
/* eslint-enable */

export default function App () {

    const [
            content,
            setMenuContent
        ] = useState(null),

        setMenu = (dom) => {

            setMenuContent(dom);

        };

    return (
        <div className="App">
            {/* eslint-disable-next-line max-statements-per-line,max-len */}
            <Layout setMenu={setMenu} menuOpen={content != null}>
                <Switch>
                    {/* eslint-disable-next-line max-len */}
                    <Route exact path="/v/:id" render={(props) => <VideoPlayer setMenu={setMenu} menuOpen={content != null} {...props}/>
                    }
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
