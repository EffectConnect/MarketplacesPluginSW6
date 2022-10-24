import template from './effectconnect-connection-overview.twig';

Shopware.Component.register('effectconnect-connection-overview', {
    inject: ['EffectConnectConnectionService'],

    data() {
        return {
            connections: [],
            error:null,
            columns: [
                { property: 'id', visible: false },
                { property: 'name', label: 'Name' },
                { property: 'salesChannelReference', label: 'Sales channel' }
            ],
            showModal: false,
            selectedItem: null,
        };
    },

    created() {
        this.refresh();
    },

    methods: {
        tc(key) {
            return this.$tc('ec.connection.overview.' + key);
        },
        refresh() {
            this.connections = [];
            this.EffectConnectConnectionService.getAll().then((data) => {
                for(let connection of data.connections) {
                    this.connections.push(connection);
                }
            });
        },
        showDeleteModal(item) {
            this.showModal = true;
            this.selectedItem = item;
        },
        closeDeleteModal() {
            this.showModal = false;
            this.selectedItem = null;
        },
        deleteItem(id) {
            this.EffectConnectConnectionService.delete(id).then(() => {
                this.closeDeleteModal();
                this.refresh();
            });
        },
        handleError(error) {
            this.error = error;
            setTimeout(() => this.error=null, 3000);
        },
    },

    template: template
});
