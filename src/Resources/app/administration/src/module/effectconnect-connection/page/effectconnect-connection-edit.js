import template from './effectconnect-connection-edit.twig';

class Toast {
    constructor(message, type) {
        this.message = message;
        this.type = type;
    }
    static error(message) {
        return new this(message, 'error');
    }
    static success(message) {
        return new this(message, 'success');
    }
}

Shopware.Component.register('effectconnect-connection-edit', {
    inject: ['EffectConnectConnectionService', 'EffectConnectTaskService'],

    data() {
        return {
            toasts: [],
            loaded: false,
            busySaving:false,
            id: this.$route.params.id,
            newItem: false,
            salesChannels: [],
            availableSalesChannels: [],
            connection: null,
            schedules: {
                offer: this._toOptions('schedules', [86400, 3600, 1800, 900, 300, 0]),
                catalog: this._toOptions('schedules', [86400, 64800, 43200, 21600, 3600, 0]),
                order: this._toOptions('schedules', [86400, 3600, 1800, 900, 300, 0]),
            },
            stockTypes: this._toOptions('stockTypes', ['salableStock', 'physicalStock']),
            paymentStatuses: this._toOptions('paymentStatuses', ['paid', 'open']),
            orderStatuses: this._toOptions('orderStatuses', ['open', 'in_progress']),
        };
    },

    created() {
        this.loaded = false;
        this.EffectConnectConnectionService.getSalesChannelData()
            .then((salesChannelData) => {
                this.initConnection(salesChannelData);
            })
            .catch((e) => this.handleError(e));
    },

    methods: {
        save() {
            let requiredFields = {name: 'Name', salesChannelId: 'Sales channel'};
            for(let requiredField of Object.keys(requiredFields)) {
                if (!this.connection[requiredField]) {
                    this.handleError(requiredFields[requiredField] + ' is required.');
                    return;
                }
            }
            this.busySaving = true;
            this.EffectConnectConnectionService.save(this.connection).then((data) => {
                if (this.newItem) {
                    this.$router.push({
                        name: 'effectconnect.connection.edit',
                        params: { id: data.id },
                    });
                    window.location.reload();
                } else {
                    this.showToast(Toast.success(this.$tc('ec.global.successSaved')), 1500);
                }
            }).catch((e) => this.handleError(e))
                .finally(() => this.busySaving = false);
        },
        initConnection(salesChannelData) {
            if (this.id) {
                this.EffectConnectConnectionService.get(this.id).then((connectionData) => {
                    this.connection = connectionData.connection;
                    this.availableSalesChannels = salesChannelData.all.filter(x => x.value === this.connection.salesChannelId);
                    this.loaded = true;
                }).catch((e) => this.handleError(e));
            } else {
                this.newItem = true;
                this.availableSalesChannels = salesChannelData.available;
                if (this.availableSalesChannels.length === 0) {
                    this.showToast(Toast.error(this.tc('noSalesChannelsAvailable')))
                    return;
                }
                this.EffectConnectConnectionService.getDefaultSettings().then((defaultSettingsData) => {
                    this.connection = defaultSettingsData.data;
                    this.loaded = true;
                }).catch((e) => this.handleError(e));
            }
        },
        showToast(toast, timeout = null) {
            this.toasts.push(toast);
            if (timeout) {
                setTimeout(() => this.toasts.splice(this.toasts.indexOf(toast), 1), timeout);
            }
        },
        triggerCatalogExport() {
            this.EffectConnectTaskService.trigger(this.connection.salesChannelId, 'catalog');
        },
        handleError(error) {
            this.showToast(Toast.error(error), 3000)
        },
        tc(key) {
            return this.$tc('ec.connection.edit.' + key);
        },
        _toOption(category, value) {
            return {value: value, label: this.tc(category + "." + value)};
        },
        _toOptions(category, values) {
            return values.map(x => this._toOption(category, x));
        },
    },

    template: template
});
