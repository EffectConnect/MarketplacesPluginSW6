import nlNL from './snippet/nl-NL';
import enGB from './snippet/en-GB';

import './page/effectconnect-log-overview';

Shopware.Module.register('effectconnect-log', {
    type: 'plugin',
    name: 'effectconnect-log',
    title: 'EffectConnect Marketplaces - Logs',
    description: 'EffectConnect log',
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
            component: 'effectconnect-log-overview',
            path: 'overview',
        }
    },

    navigation: [{
        id: 'effectconnect-log',
        label: 'Logs',
        color: '#ff3d58',
        path: 'effectconnect.log.overview',
        icon: 'default-shopping-paper-bag-product',
        parent: 'effectconnect-module',
        position: 100
    }]
});