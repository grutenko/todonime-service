import React from 'react';

import Avatar from "@material-ui/core/Avatar";
import PermIdentityIcon from '@material-ui/icons/PermIdentity';
import IconButton from "@material-ui/core/IconButton";
import Button from "@material-ui/core/Button";

import './UserButton.css';

export default function UserButton({user, onClick}) {
    return user
        ? <Button
            startIcon={<Avatar alt={user.nickname} src={user.avatar}/>}
            onClick={onClick}
            size="small"
        >
            <span className="user__name">{user.nickname}</span>
        </Button>
        : <IconButton onClick={onClick}>
            <PermIdentityIcon style={{color: 'white'}}/>
        </IconButton>
}