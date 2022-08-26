import EffectConnectApiService from "../service/effectconnect-api-service";

export default class EffectConnectLogService extends EffectConnectApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'log');
    }

    /**
     * @returns {Promise|Object}
     */
    downloadFiles(filename) {
        let config = {
            responseType:'blob'
        };
        return this.handlePromise(this.httpClient.get(this.getUri('downloadFiles', {filenames: filename}), this.getData(config)));
    }

    /**
     * @returns {Promise|Object}
     */
    getAll() {
        return this.getCall('getAll');
    }
}