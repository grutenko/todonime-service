import React from "react";
import Button from "@material-ui/core/Button";
import {fetch} from "../../lib/api";

export default function UserInfo () {

    const [
            user,
            setUser
        ] = React.useState(null),
        [
            load,
            setLoad
        ] = React.useState(false);

    if (!load) {

        fetch("user/current").then((data) => {

            setUser(!data.error ? data.data.user : null);
            setLoad(true);

        })
            .catch((data) => {

                setUser(null); setLoad(true);

            });

    }

    return <>
        {load
            ? user == null
                ? <span style={{"margin": "auto",
                    "padding": "80px 0"}}>
                    <Button
                        variant="contained"
                        color="primary"
                        href={`${process.env.REACT_APP_AUTH_BASE}?back_url=${window.location}`}
                    >
                        Авторизация через shikimori.one
                    </Button>
                </span>
                : user.nickname
            : null
        }
    </>;

}
