const { Application } = Shopware;
const initContainer = Application.getContainer('init');

import EffectConnectConnectionService from "./effectconnect-connection-service";

function createServices() {
    Application.addServiceProvider(
        'EffectConnectConnectionService',
        (container) => new EffectConnectConnectionService(initContainer.httpClient, container.loginService)
    );
}

export default createServices;