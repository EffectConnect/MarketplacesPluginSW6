import template from './effectconnect-log-overview.twig';

Shopware.Component.register('effectconnect-log-overview', {
    inject: ['EffectConnectLogService'],

    data() {
        return {
            files: [],
            columns: [
                { property: 'filename', label: this.tc('filename'), allowResize: false },
                { property: 'lastUpdated', label: this.tc('lastUpdated'), allowResize: false },
                { property: 'fullPath', label: this.tc('fullPath'), visible: false },
                { property: 'id', label: this.tc('id'), visible: false }
            ],
        };
    },

    created() {
        this.refresh();
    },

    methods: {
        tc(key) {
            return this.$tc('ec.log.overview.' + key);
        },
        refresh() {
            this.EffectConnectLogService.getAll().then(data => {
                for(let file of data.files) {
                    this.files.push({
                        id: file.path + '/' + file.filename,
                        fullPath: file.path + '/' + file.filename,
                        lastUpdated: new Date(file.lastUpdatedAt * 1000),
                        filename: file.filename
                    });
                }
            });
        },
        shouldDisplayDownloadButton() {
            let logGrid = this.$refs['logGrid'];
            let selected = logGrid ? logGrid.selectionCount : 0;
            return selected > 1;
        },
        downloadLogFile(filepath) {
            let split = filepath.split('/');
            let filename = split[split.length-1];
            this._downloadFiles(filepath, filename);
        },
        downloadSelectedLogFiles() {
            let filenames = Object.values(this.$refs['logGrid'].selection).map((file) => {return file.fullPath;}).join(',');
            this._downloadFiles(filenames, 'EC-Logfiles '+new Date().toISOString()+'.zip');
        },
        _downloadFiles(filenames, filename) {
            this.EffectConnectLogService.downloadFiles(filenames).then(data => {
                const url = window.URL.createObjectURL(new Blob([data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
            })
        },
    },

    template: template
});
