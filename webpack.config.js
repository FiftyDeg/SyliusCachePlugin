const path = require('path');
const Encore = require('@symfony/webpack-encore');

// Admin config

Encore
  .addEntry('fiftydegsyliuscachepluigin-admin', path.resolve(__dirname, './src/Resources/assets/admin/flushCache.js'))
  .setOutputPath('public/fifty-deg/sylius-cache-plugin/admin')
  .setPublicPath('/fifty-deg/sylius-cache-plugin/admin')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

const fiftyDegSyliusCachePluginAdmin = Encore.getWebpackConfig();
fiftyDegSyliusCachePluginAdmin.name = 'fiftyDegSyliusCachePluginAdmin';

Encore.reset();

module.exports = [fiftyDegSyliusCachePluginAdmin];
