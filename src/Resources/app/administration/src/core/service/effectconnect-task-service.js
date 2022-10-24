import EffectConnectApiService from "../service/effectconnect-api-service";

export default class EffectConnectTaskService extends EffectConnectApiService {
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