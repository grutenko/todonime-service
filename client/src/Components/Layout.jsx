import React from "react";

/* eslint-disable */
import {makeStyles} from "@material-ui/core/styles";
import AppBar from "@material-ui/core/AppBar";
import clsx from "clsx";
import IconButton from "@material-ui/core/IconButton";
import AccountCircle from "@material-ui/icons/AccountCircle"
import Toolbar from "@material-ui/core/Toolbar";
import Typography from "@material-ui/core/Typography";
import useTheme from "@material-ui/core/styles/useTheme";
import Container from "@material-ui/core/Container";

import UserInfo from './User/UserInfo';
/* eslint-enable */

const drawerWidth = 350,

    /* eslint-disable */
    useStyles = makeStyles((theme) => ({
        "root": {
            "flexGrow": 1
        },
        "main": {
            "width": "calc(100vw - 100px)"
        },
        "appBarShift": {
            "marginRight": drawerWidth,
            "transition": theme.transitions.create(
                [
                    "margin",
                    "width"
                ],
                {
                    "duration": theme.transitions.duration.enteringScreen,
                    "easing": theme.transitions.easing.easeOut
                }
            ),
            "width": `calc(100% - ${drawerWidth}px)`
        },
        "menuButton": {
            "marginRight": theme.spacing(0, 1),
            "zIndex": "10000"
        },
        "title": {
            "flexGrow": 1
        },
        "toolbar": {
            "background": "#424848"
        },
        "hide": {
            "display": "none"
        },
        "drawer": {
            "width": drawerWidth,
            "flexShrink": 0
        },
        "drawerPaper": {
            "width": drawerWidth
        },
        "drawerHeader": {
            "display": "flex",
            "alignItems": "center",
            "padding": 0,
            "minHeight": 48,
            "justifyContent": "flex-start"
        },
        "content": {
            "flexGrow": 1,
            "padding": theme.spacing(3),
            "transition": theme.transitions.create(
                "margin",
                {
                    "easing": theme.transitions.easing.sharp,
                    "duration": theme.transitions.duration.leavingScreen
                }
            ),
            "marginRight": -drawerWidth
        },
        "contentShift": {
            "transition": theme.transitions.create(
                "margin",
                {
                    "easing": theme.transitions.easing.easeOut,
                    "duration": theme.transitions.duration.enteringScreen
                }
            ),
            "marginRight": 0
        }
    }));
/* eslint-enable */

// eslint-disable-next-line max-lines-per-function
export default function Layout ({title, setMenu, menuOpen, children}) {

    const classes = useStyles();

    return <div className={classes.root}>
        <AppBar
            position="static"
            className={clsx(
                classes.appBar,
                {
                    [classes.appBarShift]: menuOpen
                }
            )}
        >
            <Toolbar variant="dense" className={classes.toolbar}>
                <Typography
                    variant="div"
                    color="inherit"
                    className={classes.title}
                >
                    {title}
                </Typography>
                <IconButton
                    edge="end"
                    className={classes.menuButton}
                    color="inherit"
                    aria-label="menu"
                    onClick={() => setMenu(<UserInfo/>)}
                >
                    <AccountCircle />
                </IconButton>
            </Toolbar>
        </AppBar>
        <Container maxWidth={false} className={classes.main}>
            {children}
        </Container>
    </div>;

}
