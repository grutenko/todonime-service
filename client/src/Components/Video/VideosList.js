import React from "react";

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
import {withRouter} from "react-router-dom";
import CheckIcon from "@material-ui/icons/Check";

export default class VideosList extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            "type": this.props.currentKind,
            "count": {
                "dub": 0,
                "sub": 0,
                "org": 0
            }
        };

    }

    componentDidMount () {

        this.calc();

    }

    componentDidUpdate (prevProps, prevState) {

        if (prevProps.currentId !== this.props.currentId) {

            this.calc();
            this.forceUpdate();

        }

    }

    calc () {

        const count = {
            "dub": 0,
            "sub": 0,
            "org": 0
        };

        for (const video of this.props.videos) {

            count[video.kind] += 1;

        }

        this.setState({count});

    }

    render () {

        const {
                count,
                type
            } = this.state,
            {
                videos,
                currentId
            } = this.props;

        return <>
            <div style={{"marginBottom": "15px"}}>
                <ToggleButtonGroup
                    size="small"
                    value={type}
                    exclusive
                    onChange={(event, value) => this.setState({"type": value})}
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
            <AuthorsListWithRouter currentId={currentId} videos={videos} kind={type}/>
        </>;

    }

}

class AuthorsList extends React.Component {

    constructor (props) {

        super(props);

        this.state = {
            domains     : this.setDomains(props.videos),
            currentId   : props.currentId
        };

    }

    componentDidUpdate (prevProps, prevState) {
        if (prevProps.currentId !== this.props.currentId || prevProps.kind !== this.props.kind) {
            this.setState({
                domains: this.setDomains(this.props.videos),
                currentId: this.props.currentId
            });
        }
    }

    onClickVideo(id) {
        return () => {
            this.setState({
                currentId: id
            });
            this.props.history.push(`/v/${id}`);
        }
    }

    setDomains (videos) {

        const domains = {};

        for (const video of videos) {

            if (video.kind !== this.props.kind) {

                // eslint-disable-next-line no-continue
                continue;

            }

            if (domains[video.domain] === undefined) {

                domains[video.domain] = [];

            }

            domains[video.domain].push(video);

        }

        return domains;

    }

    render () {

        const {
                domains,
                currentId
            } = this.state;

        return (
            <div style={{"width": "100%"}}>
                {Object.entries(domains).map(([
                    domain,
                    authors
                ], i) => <ExpansionPanel key={i}>
                    <ExpansionPanelSummary
                        expandIcon={<ExpandMoreIcon />}
                        aria-controls="panel1a-content"
                        id="panel1a-header"
                    >
                        <Typography>
                            <img style={{"marginRight": "5px",
                                "verticalAlign": "middle"}}
                                 src={`https://www.google.com/s2/favicons?domain=${domain}`}
                                 alt={domain}
                            />
                            {domain}
                        </Typography>
                    </ExpansionPanelSummary>
                    <ExpansionPanelDetails>
                        <List style={{"flex": 1}} component="div" aria-label="main mailbox folders">
                            {authors.map((video, i) => <ListItem key={video._id.$oid}
                                button
                                selected={video._id.$oid === currentId}
                                onClick={this.onClickVideo(video._id.$oid)}
                                style={{"display": "flex"}}
                            >
                                <ListItemText
                                    primary={<>
                                        <span style={{"verticalAlign": "middle",
                                            "marginRight": "5px"}}>
                                            <img
                                                src={`/static/img/flags/flags-iso/flat/16/${{"ru": "RU",
                                                    "en": "EN",
                                                    "ja": "JP"}[video.language] || "RU"}.png`}
                                                alt="Canada Flag"
                                            />
                                        </span>
                                        <span style={{"fontSize": "13px"}}>
                                            {video.author.substr(0,22 ) + (video.author.length > 22 ? "..." : "")}
                                        </span>
                                    </>}
                                />
                                {
                                    video.completed
                                        ? <CheckIcon color="primary" title="Сериал переведен этим проектом полностью"/>
                                        : null
                                }
                            </ListItem>)}
                        </List>
                    </ExpansionPanelDetails>
                </ExpansionPanel>)}
            </div>);

    }
}

const AuthorsListWithRouter = withRouter(AuthorsList);
