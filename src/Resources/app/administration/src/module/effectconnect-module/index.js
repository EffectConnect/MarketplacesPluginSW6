import nlNL from './snippet/nl-NL';
import enGB from './snippet/en-GB';

Shopware.Module.register('effectconnect-module', {
    type: 'plugin',
    name: 'effectconnect-module',
    title: 'EffectConnect',
    description: 'EffectConnect module',
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
        dummy: {
            component: { template: '' },
            path: 'effectconnect.log',
        }
    },

    navigation: [{
        id: 'effectconnect-module',
        label: 'EffectConnect',
        color: '#ff3d58',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-extension',
        position: 100
    }]
});