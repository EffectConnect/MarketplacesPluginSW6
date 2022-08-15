import nlNL from './snippet/nl-NL';
import enGB from './snippet/en-GB';

import './page/effectconnect-connection-overview';
import './page/effectconnect-connection-edit';

Shopware.Module.register('effectconnect-connection', {
    type: 'plugin',
    name: 'effectconnect-connection',
    title: 'Connection',
    description: 'EffectConnect connection',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'default-action-settings',
    entity: 'integration',

    snippets: {
        'nl-NL': nlNL,
        'en-GB': enGB
    },

    routes: {
        overview: {
            component: 'effectconnect-connection-overview',
            path: 'overview',
        },
        edit: {
            component: 'effectconnect-connection-edit',
            path: 'edit/:id',
            meta: {
                parentPath: 'effectconnect-connection.overview',
                parentRoute: 'effectconnect-connection.overview'
            }
        },
        create: {
            component: 'effectconnect-connection-edit',
            path: 'create',
            meta: {
                parentPath: 'effectconnect-connection.overview',
                parentRoute: 'effectconnect-connection.overview'
            }
        }

    },

    navigation: [{
        id: 'effectconnect-connection',
        label: 'Connections',
        color: '#ff3d58',
        path: 'effectconnect.connection.overview',
        icon: 'default-shopping-paper-bag-product',
        parent: 'effectconnect-module',
        position: 100
    }]
});