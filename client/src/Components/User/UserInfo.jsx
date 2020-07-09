import React from "react";
import Button from "@material-ui/core/Button";
import {fetch} from "../../lib/api";
import { setShow } from "../../lib/promt";

import './UserInfo.css';
import Avatar from "@material-ui/core/Avatar";
import Chip from "@material-ui/core/Chip";

import WatchList from '../Anime/WatchList';

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
            </span>
            <Scopes scopes={user.scope}/>
        </div>
    }

    render() {
        return <div>
            { this.renderUserData() }
            <WatchList />
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