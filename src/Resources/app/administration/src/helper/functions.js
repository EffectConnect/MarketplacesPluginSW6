
function _versionToFloat(version) {
    version = version.replace(/\D/g,'');
    return parseFloat(version.substring(0, 1) + "." + version.substring(1, version.length));
}

export function versionIsBefore(version) {
    const swVersion = _versionToFloat(Shopware.Context.app.config.version);
    return swVersion < version;
}