import template from './effectconnect-connection-edit.twig';

Shopware.Component.register('effectconnect-connection-edit', {
    inject: ['EffectConnectConnectionService'],

    data() {
        return {
            loaded: false,
            error: null,
            showSuccess: false,
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
                    this.showSuccess = true;
                    setTimeout(() => this.showSuccess=false, 1500);
                }
            }).catch((e) => this.handleError(e)).finally(() => this.busySaving = false);
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
                    this.error = this.tc('noSalesChannelsAvailable');
                    return;
                }
                this.EffectConnectConnectionService.getDefaultSettings().then((defaultSettingsData) => {
                    this.connection = defaultSettingsData.data;
                    this.loaded = true;
                }).catch((e) => this.handleError(e));
            }
        },
        handleError(error) {
            this.error = error;
            setTimeout(() => this.error=null, 3000);
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
