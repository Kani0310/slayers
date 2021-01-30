import {connect} from 'react-redux';
import React from 'react';
import PropTypes from 'prop-types';
import APIController from './APIController.react';
import Loading from './components/core/Loading.react';
import Toolbar from './components/core/Toolbar.react';
import Reloader from './components/core/Reloader.react';
import {setHooks, setConfig} from './actions/index';
import {type} from 'ramda';

class UnconnectedAppContainer extends React.Component {
    constructor(props) {
        super(props);
        if (
            props.hooks.request_pre !== null ||
            props.hooks.request_post !== null
        ) {
            props.dispatch(setHooks(props.hooks));
        }
    }

    UNSAFE_componentWillMount() {
        const {dispatch} = this.props;
        const config = JSON.parse(
            document.getElementById('_dash-config').textContent
        );

        // preset common request params in the config
        config.fetch = {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json'
            }
        };

        dispatch(setConfig(config));
    }

    render() {
        const {config} = this.props;
        if (type(config) === 'Null') {
            return <div className='_dash-loading'>Loading...</div>;
        }
        const {show_undo_redo} = config;
        return (
            <React.Fragment>
                {show_undo_redo ? <Toolbar /> : null}
                <APIController />
                <Loading />
                <Reloader />
            </React.Fragment>
        );
    }
}

UnconnectedAppContainer.propTypes = {
    hooks: PropTypes.object,
    dispatch: PropTypes.func,
    config: PropTypes.object
};

const AppContainer = connect(
    state => ({
        history: state.history,
        config: state.config
    }),
    dispatch => ({dispatch})
)(UnconnectedAppContainer);

export default AppContainer;
