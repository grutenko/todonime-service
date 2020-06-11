import React from 'react';

import Avatar from "@material-ui/core/Avatar";
import PermIdentityIcon from '@material-ui/icons/PermIdentity';
import IconButton from "@material-ui/core/IconButton";
import Button from "@material-ui/core/Button";

export default function UserButton({user, onClick}) {
    const styles = {
        root: {
            display: 'inline-flex'
        },
        name: {
            color: 'white',
            margin: 'auto 5px'
        }
    };

    return user
        ? <Button startIcon={<Avatar alt={user.nickname} src={user.avatar}/>} onClick={onClick} size="small">
            <span style={styles.name}>{user.nickname}</span>
        </Button>
        : <IconButton onClick={onClick}>
            <PermIdentityIcon style={{color: 'white'}}/>
        </IconButton>
}