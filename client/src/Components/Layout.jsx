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

/* eslint-enable */

const drawerWidth = 350,

    /* eslint-disable */
    useStyles = makeStyles((theme) => ({
        "root": {
            "flexGrow": 1,
            "padding": 0,
            "margin": 0
        },
        "main": {
            "padding": 0,
            "margin": 0
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
        <Container maxWidth={false} className={classes.main}>
            {children}
        </Container>
    </div>;

}
