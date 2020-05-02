import React from "react";

import {makeStyles} from "@material-ui/core/styles";
import ExpansionPanel from "@material-ui/core/ExpansionPanel";
import ExpansionPanelSummary from "@material-ui/core/ExpansionPanelSummary";
import ExpansionPanelDetails from "@material-ui/core/ExpansionPanelDetails";
import Typography from "@material-ui/core/Typography";
import ExpandMoreIcon from "@material-ui/icons/ExpandMore";
import SubtitlesIcon from "@material-ui/icons/Subtitles";
import RecordVoiceOverIcon from "@material-ui/icons/RecordVoiceOver";
import TranslateIcon from "@material-ui/icons/Translate";
import ToggleButton from "@material-ui/lab/ToggleButton";
import ToggleButtonGroup from "@material-ui/lab/ToggleButtonGroup";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import Badge from "@material-ui/core/Badge";
import {Link} from "react-router-dom";
import Flag from "react-flags";
import ReportProblemIcon from "@material-ui/icons/ReportProblem";

const useStyles = makeStyles((theme) => ({
    "root": {
        "width": "100%"
    },
    "heading": {
        "fontSize": theme.typography.pxToRem(15),
        "fontWeight": theme.typography.fontWeightRegular
    }
}));

export default function VideosList ({currentId, videos}) {

    const [
            type,
            setType
        // eslint-disable-next-line no-magic-numbers
        ] = React.useState("dub"),

        count = {
            "dub": 0,
            "sub": 0,
            "org": 0
        };
    for (const video of videos) {

        count[video.kind] += 1;

    }

    return <>
        <div style={{"marginBottom": "15px"}}>
            <ToggleButtonGroup
                size="small"
                value={type}
                exclusive
                onChange={(event, value) => setType(value)}
            >
                <ToggleButton key={1} value="dub">
                    <Badge badgeContent={count.dub.toString()} color={count.dub > 0 ? "secondary" : "default"}>
                        <RecordVoiceOverIcon />
                    </Badge>

                </ToggleButton>
                <ToggleButton key={2} value="sub">
                    <Badge badgeContent={count.sub.toString()} color={count.sub > 0 ? "secondary" : "default"}>
                        <SubtitlesIcon />
                    </Badge>
                </ToggleButton>
                <ToggleButton key={3} value="org">
                    <Badge badgeContent={count.org.toString()} color={count.org > 0 ? "secondary" : "default"}>
                        <TranslateIcon />
                    </Badge>
                </ToggleButton>
            </ToggleButtonGroup>
        </div>
        <AuthorsList currentId={currentId} videos={videos} kind={type}/>
    </>;

}

function AuthorsList ({currentId, videos, kind}) {

    const classes = useStyles(),

        domains = {};
    for (const video of videos) {

        if (video.kind !== kind) {

            // eslint-disable-next-line no-continue
            continue;

        }

        if (domains[video.domain] === undefined) {

            domains[video.domain] = [];

        }

        domains[video.domain].push(video);

    }

    return (
        <div className={classes.root}>
            {Object.entries(domains).map(([
                domain,
                authors
            ], i) => <ExpansionPanel key={i}>
                <ExpansionPanelSummary
                    expandIcon={<ExpandMoreIcon />}
                    aria-controls="panel1a-content"
                    id="panel1a-header"
                >
                    <Typography className={classes.heading}>
                        <img style={{"marginRight": "5px",
                            "verticalAlign": "middle"}} src={`https://www.google.com/s2/favicons?domain=${domain}`} />
                        {domain}
                    </Typography>
                </ExpansionPanelSummary>
                <ExpansionPanelDetails>
                    <List style={{"flex": 1}} component="div" aria-label="main mailbox folders">
                        {authors.map((video, i) => <ListItem key={i}
                            button
                            selected={video._id.$oid === currentId}
                            onClick={() => {}}
                            style={{"display": "flex"}}
                        >
                            <ListItemText primary={<>
                                <span style={{"verticalAlign": "middle",
                                    "marginRight": "5px"}}>
                                    <Flag
                                        name={{"russian": "RUS",
                                            "english": "ENG",
                                            "original": "JP"}[video.language] || "RUS"}
                                        format="png"
                                        pngSize={16}
                                        basePath="/static/img/flags"
                                        alt="Canada Flag"
                                    />
                                </span>
                                <Link to={`/v/${video._id.$oid}`} style={{"flex": 1}}>
                                    <span style={{"fontSize": "13px"}}>{video.author.substr(
                                        0,
                                        22
                                    ) + (
                                        video.author.length > 22 ? "..." : ""
                                    )}</span>
                                </Link>
                            </>}/>
                            <ReportProblemIcon className="report-problem" color="secondary"/>
                        </ListItem>)}
                    </List>
                </ExpansionPanelDetails>
            </ExpansionPanel>)}
        </div>);

}
