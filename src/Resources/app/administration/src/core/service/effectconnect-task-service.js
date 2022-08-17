import EffectConnectApiService from "../service/effectconnect-api-service";

class EffectConnectTaskService extends EffectConnectApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'task');
    }

    /**
     * @returns {Promise|Object}
     */
    trigger(salesChannelId, type) {
        return this.postCall('trigger/'+salesChannelId+'/'+type);
    }
}

export default EffectConnectTaskService;
