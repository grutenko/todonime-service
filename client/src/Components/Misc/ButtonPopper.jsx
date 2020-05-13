import React from "react";
import {IconButton, makeStyles} from "@material-ui/core";
import Typography from "@material-ui/core/Typography";
import Popover from "@material-ui/core/Popover";

const useStyles = makeStyles((theme) => ({
    "popover": {
        "pointerEvents": "none",
        "padding": 10,
        "margin": 10
    },
    "paper": {
        "padding": theme.spacing(1)
    }
}));


export default function ButtonPopper ({text, children}) {

    const classes = useStyles(),

        [
            anchorEl,
            setAnchorEl
        ] = React.useState(null),

        handlePopoverOpen = (event) => {

            setAnchorEl(event.currentTarget);

        },

        handlePopoverClose = () => {

            setAnchorEl(null);

        };
    return <>
        <IconButton
            aria-describedby="mouse-over-popover"
            variant="contained"
            onMouseEnter={handlePopoverOpen}
            onMouseLeave={handlePopoverClose}
        >
            {children}
        </IconButton>
        <Popover
            id="mouse-over-popover"
            open={anchorEl != null}
            className={classes.popover}
            classes={{
                "paper": classes.paper
            }}
            anchorEl={anchorEl}
            anchorOrigin={{
                "vertical": "bottom",
                "horizontal": "left"
            }}
            transformOrigin={{
                "vertical": "top",
                "horizontal": "left"
            }}
            onClose={handlePopoverClose}
            disableRestoreFocus
        >
            <Typography>{text}</Typography>
        </Popover>
    </>;

}
