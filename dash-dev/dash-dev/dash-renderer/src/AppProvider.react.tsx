import PropTypes from 'prop-types';
import React, {useState} from 'react';
import {Provider} from 'react-redux';

import Store from './store';
import AppContainer from './AppContainer.react';

const AppProvider = ({hooks}: any) => {
    const [{store}] = useState(() => new Store());

    return (
        <Provider store={store}>
            <AppContainer hooks={hooks} />
        </Provider>
    );
};

AppProvider.propTypes = {
    hooks: PropTypes.shape({
        request_pre: PropTypes.func,
        request_post: PropTypes.func
    })
};

AppProvider.defaultProps = {
    hooks: {
        request_pre: null,
        request_post: null
    }
};

export default AppProvider;
