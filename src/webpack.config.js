const path = require('path');
const Encore = require('@symfony/webpack-encore');

// Admin config

Encore
  .addEntry('fiftydegsyliuscachepluigin-admin', path.resolve(__dirname, './Resources/assets/admin/flushCache.js'))
  .setOutputPath('public/bundles/fiftydeg-cache/admin')
  .setPublicPath('/bundles/fiftydeg-cache/admin')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

const fiftyDegSyliusCachePluginAdmin = Encore.getWebpackConfig();
fiftyDegSyliusCachePluginAdmin.name = 'fiftyDegSyliusCachePluginAdmin';

Encore.reset();

module.exports = [fiftyDegSyliusCachePluginAdmin];
