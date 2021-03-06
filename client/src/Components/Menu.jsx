import React from "react";

/* eslint-disable */
import {makeStyles} from "@material-ui/core/styles";
import ChevronLeftIcon from "@material-ui/icons/ChevronLeft";
import ChevronRightIcon from "@material-ui/icons/ChevronRight";
import Drawer from "@material-ui/core/Drawer";
import IconButton from "@material-ui/core/IconButton";
import useTheme from "@material-ui/core/styles/useTheme";
import Container from "@material-ui/core/Container";
import ClickAwayListener from "@material-ui/core/ClickAwayListener";
/* eslint-enable */


export const drawerWidth = 450;

/* eslint-disable */
    const useStyles = makeStyles((theme) => ({

        "toolbar": {
            "background": "#424848"
        },
        "hide": {
            "display": "none"
        },
        "drawer": {
            "width": drawerWidth,
            "flexShrink": 0,
            [theme.breakpoints.down('sm')]: {
                "width": '100vw'
            },
        },
        "drawerPaper": {
            "width": drawerWidth,
            [theme.breakpoints.down('sm')]: {
                "width": '100vw'
            },
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

export default function Menu(props) {
    const classes = useStyles(),
        [
            open,
            setOpen
        ] = React.useState(props.isShow || false);

    if (props.isShow && !open) {
        setOpen(true);
    } else if (!props.isShow && open) {
        setOpen(false);
    }

    const theme = useTheme();

    return <Drawer
            className   = {classes.drawer}
            variant     = "temporary"
            anchor      = "right"
            open        = {open}
            ModalProps  = {{
                onBackdropClick: props.onClose,
                onEscapeKeyDown: props.onClose
            }}
            PaperProps={{
                className: classes.drawerPaper
            }}
            classes     = {{
                "paper": 'menu-scrollable'
            }}
        >
            <div className={classes.drawerHeader}>
                <IconButton onClick={props.onClose}>
                    <ChevronRightIcon/>
                </IconButton>
            </div>
            <Container className="menu-container" style={{padding: '0 0 0 5px'}}>
                {props.children || <div/>}
            </Container>
        </Drawer>
}