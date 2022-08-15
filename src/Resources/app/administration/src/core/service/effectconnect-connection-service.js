import EffectConnectApiService from "../service/effectconnect-api-service";

class EffectConnectConnectionService extends EffectConnectApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'connection');
    }

    /**
     * @returns {Promise|Object}
     */
    getAll() {
        return this.getCall('getAll');
    }

    /**
     * @returns {Promise|Object}
     */
    get(id) {
        return this.getCall('get/'+id);
    }

    /**
     * @returns {Promise|Object}
     */
    getSalesChannelData() {
        return this.getCall('getSalesChannelData');
    }

    /**
     * @returns {Promise|Object}
     */
    getDefaultSettings() {
        return this.getCall('getDefaultSettings');
    }

    /**
     * @returns {Promise|Object}
     */
    delete(id) {
        return this.postCall('delete/'+id);
    }

    /**
     * @returns {Promise|Object}
     */
    save(connection) {
        return this.postCall('save', {connection: connection});
    }
}

export default EffectConnectConnectionService;
