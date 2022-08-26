const ApiService = Shopware.Classes.ApiService;

export default class EffectConnectApiService extends ApiService {
    /**
     * @constructor
     * @param {AxiosInstance} httpClient
     * @param loginService
     * @param category
     */
    constructor(httpClient, loginService, category) {
        super(httpClient, loginService, 'ec/action');
        this.category = category;
    }

    _getBaseUri() {
        return 'ec/action' + (this.category ? '/' + this.category : '');
    }

    getUri(endpoint, queryParams) {
        let uri = this._getBaseUri() + '/' + endpoint;
        if (!queryParams) {
            return uri;
        }

        let flattened = Object.entries(queryParams).map(([k,v]) => `${k}=${v}`);
        return uri +  "?" + flattened.join('&');
    }

    handleResponse(response) {
        return ApiService.handleResponse(response);
    }

    getData(config) {
        let headers = this.getBasicHeaders();
        return {...config, headers};
    }

    handlePromise(promise) {
        return promise.then(r => this.handleResponse(r));
    }

    getCall(endpoint) {
        return this.handlePromise(this.httpClient.get(this.getUri(endpoint), this.getData({})));
    }

    postCall(endpoint, data = {}) {
        return this.handlePromise(this.httpClient.post(this.getUri(endpoint), data, this.getData({})));
    }

}
