import React from "react";
import Button from "@material-ui/core/Button";
import {fetch} from "../../lib/api";
import { setShow } from "../../lib/promt";

import './UserInfo.css';
import Avatar from "@material-ui/core/Avatar";
import Chip from "@material-ui/core/Chip";

import WatchList from '../Anime/WatchList';
import { IconButton } from "@material-ui/core";
import ExitToAppIcon from '@material-ui/icons/ExitToApp';
import VpnKeyIcon from '@material-ui/icons/VpnKey';

export default class UserInfo extends React.Component {
    logout() {
        setShow('logout');
        fetch('user/logout', {}, 'POST').then(data => {
            window.location.href = window.location;
        });
    }

    login() {
        setShow('login');
        window.location.href = `${process.env.REACT_APP_AUTH_BASE}?back_url=${window.location}`;
    }

    renderUserData() {
        const {user} = this.props;

        return <div className="user-info__header">
            <Avatar alt={user.nickname} src={user.avatar}/>
            <span className="user-info__name">
                {user.nickname}
                <Scopes scopes={user.scope}/>
            </span>
            <IconButton onClick={this.logout.bind(this)}>
                <ExitToAppIcon />
            </IconButton>
        </div>
    }

    render() {
        return <div style={{maxWidth: "500px", margin: "25px auto"}}>
            {this.props.user
                ? <>
                    { this.renderUserData() }
                    <WatchList />
                </>
                : <div style={{display: "flex", height: "calc(100vh - 75px)"}}>
                    <div style={{margin: 'auto'}}>
                        <Button
                            onClick={this.login.bind(this)}
                            startIcon={<VpnKeyIcon/>}
                            variant="outlined"
                            color="primary"
                        >
                            Авторизация через Shikimori
                        </Button><br/>
                        <p>Позволит сохранять прогресс просмотра<br/> импортировать списки с shikimori.</p>
                    </div>
                </div>
            }
        </div>
    }
}


function Scopes({scopes}) {
    return <>
        {scopes.map(scope => <Chip
            className="comment__chip"
            color="primary"
            label={scope}
        />)}
    </>
}