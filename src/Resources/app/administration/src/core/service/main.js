const { Application } = Shopware;
const initContainer = Application.getContainer('init');

import EffectConnectLogService from "./effectconnect-log-service";

function createServices() {
    Application.addServiceProvider(
        'EffectConnectLogService',
        (container) => new EffectConnectLogService(initContainer.httpClient, container.loginService)
    );
}

export default createServices;