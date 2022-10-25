import template from './effectconnect-connection-edit.twig';

const { Mixin } = Shopware;

class Modal {
    constructor(text, onConfirm, onCancel) {
        this.text = text;
        this.onConfirm = onConfirm;
        this.onCancel = onCancel;
    }
}

Shopware.Component.register('effectconnect-connection-edit', {
    inject: ['EffectConnectConnectionService', 'EffectConnectTaskService'],

    data() {
        return {
            toasts: [],
            modal: null,
            loaded: false,
            busySaving:false,
            id: this.$route.params.id,
            newItem: false,
            salesChannels: [],
            availableSalesChannels: [],
            connection: null,
            schedules: [],
            stockTypes: [],
            paymentStatuses: [],
            orderStatuses: [],
            customerSourceTypes: [],
            shippingStatuses: [],
        };
    },

    mixins: [
        Mixin.getByName('notification'),
    ],

    created() {
        this.loaded = false;
        this.EffectConnectConnectionService.getSalesChannelData()
            .then((salesChannelData) => {
                this.initConnection(salesChannelData).then(() => {
                    this.EffectConnectConnectionService.getOptions().then((data) => {
                        data = data.data;
                        this.schedules = this._toOptions('schedules', data.schedules);
                        this.stockTypes = this._toOptions('stockTypes', data.stockTypes);
                        this.paymentStatuses = this._toOptions('paymentStatuses', data.payment);
                        this.orderStatuses = this._toOptions('orderStatuses', data.order);
                        this.customerSourceTypes = this._toOptions('customerSourceTypes', data.customerSourceTypes);
                        this.shippingStatuses = this._toOptions('shippingStatuses', data.shipping);
                        this.loaded = true;
                    })
                });
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
                    this.createNotificationSuccess({
                        message: this.$tc('ec.global.successSaved')
                    })
                }
            }).catch((e) => this.handleError(e))
                .finally(() => this.busySaving = false);
        },
        initConnection(salesChannelData) {
            if (this.id) {
                return this.EffectConnectConnectionService.get(this.id).then((connectionData) => {
                    this.connection = connectionData.connection;
                    this.availableSalesChannels = salesChannelData.data.filter(x => x.value === this.connection.salesChannelId);
                }).catch((e) => this.handleError(e));
            } else {
                this.newItem = true;
                this.availableSalesChannels = salesChannelData.data;
                return this.EffectConnectConnectionService.getDefaultSettings().then((defaultSettingsData) => {
                    this.connection = defaultSettingsData.data;
                }).catch((e) => this.handleError(e));
            }
        },
        triggerCatalogExport() {
            this.modal = new Modal('Are you sure you want to trigger this process?',
                () => {
                    this.createNotificationSuccess({message: this.tc('taskTriggered')});
                    this.EffectConnectTaskService.trigger(this.connection.salesChannelId, 'catalog');
                    this.modal = null;
                },
                () => {
                    this.modal = null;
                }
            );
        },
        testApiCredentials() {
            return this.EffectConnectConnectionService.testApiCredentials(this.connection.publicKey, this.connection.secretKey)
                .then((data) => {
                    if (data.valid) {
                        this.createNotificationSuccess({message: this.tc('apiCredentialsOK')});
                    } else {
                        this.createNotificationError({message: this.tc('apiCredentialsNOK')});
                    }
            }).catch((e) => this.handleError(e));
        },
        handleError(error) {
            this.createNotificationError({message:error});
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
