import template from './effectconnect-log-overview.twig';
import EffectConnectLogService from "../../../core/service/effectconnect-log-service";

Shopware.Component.register('effectconnect-log-overview', {
    inject: ['EffectConnectLogService'],

    data() {
        return {
            files: [],
            columns: [
                { property: 'filename', label: this.$tc('effectconnect.log.column.filename'), allowResize: false },
                { property: 'lastUpdated', label: this.$tc('effectconnect.log.column.lastUpdated'), allowResize: false },
                { property: 'fullPath', label: this.$tc('effectconnect.log.column.fullPath'), visible: false }
            ],
        };
    },

    created() {
        this.EffectConnectLogService.getLogFiles().then(data => {
            for(let file of data.files) {
                this.files.push({
                    fullPath: file.path + '/' + file.filename,
                    lastUpdated: new Date(file.lastUpdatedAt * 1000),
                    filename: file.filename
                });
            }
        });
    },

    methods: {
        shouldDisplayDownloadButton() {
            let logGrid = this.$refs['logGrid'];
            let selected = logGrid ? logGrid.selectionCount : 0;
            return selected > 1;
        },

        downloadLogFile: function(filepath) {
            let split = filepath.split('/');
            let filename = split[split.length-1];
            this._downloadFiles(filepath, filename);
        },

        downloadSelectedLogFiles: function() {
            let filenames = Object.values(this.$refs['logGrid'].selection).map((file) => {return file.fullPath;}).join(',');
            this._downloadFiles(filenames, 'EC-Logfiles '+new Date().toISOString()+'.zip');
        },

        _downloadFiles(filenames, filename) {
            this.EffectConnectLogService.downloadLogFiles(filenames).then(data => {
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
