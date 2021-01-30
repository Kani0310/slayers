import {connect} from 'react-redux';
import React, {Component} from 'react';
import PropTypes from 'prop-types';
import Radium from 'radium';
import {DebugMenu} from './menu/DebugMenu.react';

class UnconnectedGlobalErrorContainer extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        const {config, error, children} = this.props;
        return (
            <div id='_dash-global-error-container'>
                <DebugMenu error={error} hotReload={Boolean(config.hot_reload)}>
                    <div id='_dash-app-content'>{children}</div>
                </DebugMenu>
            </div>
        );
    }
}

UnconnectedGlobalErrorContainer.propTypes = {
    children: PropTypes.object,
    config: PropTypes.object,
    error: PropTypes.object
};

const GlobalErrorContainer = connect(state => ({
    config: state.config,
    error: state.error
}))(Radium(UnconnectedGlobalErrorContainer));

export default GlobalErrorContainer;
