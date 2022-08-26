const { Application } = Shopware;
const initContainer = Application.getContainer('init');

import EffectConnectConnectionService from "./effectconnect-connection-service";
import EffectConnectTaskService from "./effectconnect-task-service";
import EffectConnectLogService from "./effectconnect-log-service";

function createServices() {
    Application.addServiceProvider('EffectConnectConnectionService', (container) => {
        return new EffectConnectConnectionService(initContainer.httpClient, container.loginService);
    });
    Application.addServiceProvider('EffectConnectTaskService', (container) => {
        return new EffectConnectTaskService(initContainer.httpClient, container.loginService);
    });
    Application.addServiceProvider('EffectConnectLogService', (container) => {
        return new EffectConnectLogService(initContainer.httpClient, container.loginService);
    });
}

export default createServices;