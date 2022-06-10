const ApiService = Shopware.Classes.ApiService;

class EffectConnectApiService extends ApiService {
    constructor(httpClient, loginService, category) {
        super(httpClient, loginService, 'effectconnect/action');
        this.category = category;
    }

    _getBaseUri() {
        return 'effectconnect/action' + (this.category ? '/' + this.category : '');
    }

    getUri(endpoint, queryParams) {
        let uri = this._getBaseUri() + '/' + endpoint;
        if (!queryParams) {
            return uri;
        }

        let flattened = Object.entries(queryParams).map(([k,v]) => `${k}=${v}`);
        return uri +  "?" + flattened.join('&');
    }

    // static handleResponse(response) {
    //     return ApiService.handleResponse(response);
    // }

}

export default EffectConnectApiService;
