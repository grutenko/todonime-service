import React from "react";

import makeStyles from "@material-ui/core/styles/makeStyles";
import Backdrop from "@material-ui/core/Backdrop";
import CircularProgress from "@material-ui/core/CircularProgress";

const useStyles = makeStyles((theme) => ({
    "backdrop": {
        "color": "#fff",
        // eslint-disable-next-line no-magic-numbers
        "zIndex": theme.zIndex.drawer + 1
    }
}));

export default function Loader () {

    const classes = useStyles();

    return <Backdrop className={classes.backdrop} open={true}>
        <CircularProgress color="inherit" />
    </Backdrop>;

}
