const { Application } = Shopware;
const initContainer = Application.getContainer('init');

import EffectConnectConnectionService from "./effectconnect-connection-service";
import EffectConnectTaskService from "./effectconnect-task-service";

function createServices() {
    Application.addServiceProvider(
        'EffectConnectConnectionService',
        (container) => new EffectConnectConnectionService(initContainer.httpClient, container.loginService)
    );
    Application.addServiceProvider(
        'EffectConnectTaskService',
        (container) => new EffectConnectTaskService(initContainer.httpClient, container.loginService)
    );

}

export default createServices;