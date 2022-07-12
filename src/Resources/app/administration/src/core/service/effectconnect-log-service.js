import EffectConnectApiService from "../service/effectconnect-api-service";
const ApiService = Shopware.Classes.ApiService;

class EffectConnectLogService extends EffectConnectApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, 'log');
    }

    /**
     * @returns {Promise|Object}
     */
    getLogFiles() {
        return this.httpClient
            .get(this.getUri('getAll'), {headers: this.getBasicHeaders()})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    /**
     * @returns {Promise|Object}
     */
    downloadLogFiles(filename) {
        let headers = this.getBasicHeaders();
        let uri = this.getUri('downloadFiles', {filenames: filename});

        let config = {
            responseType: 'blob'
        };
        return this.httpClient
            .get(uri, {...config, headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default EffectConnectLogService;
