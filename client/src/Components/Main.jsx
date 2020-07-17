import React from 'react';

import {fetch} from '../lib/api';
import UserInfo from './User/UserInfo';
import CircularProgress from "@material-ui/core/CircularProgress";

export default class Main extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            load: true,
            user: null
        };
    }

    componentDidMount() {
        this.fetch();
    }

    fetch() {
        fetch("user/current").then(
            data => this.setState({load: false, user: data.data.user}),
            data => this.setState({load: false})
        );
    }

    render() {
        return !this.state.load
            ? <UserInfo user={this.state.user} />
            : <CircularProgress color="primary" style={{margin: "20px"}} />
    }
}