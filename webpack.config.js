const path = require('path');
const Encore = require('@symfony/webpack-encore');

// Admin config

Encore
  .addEntry('fiftydegsyliuscachepluigin-admin', path.resolve(__dirname, './src/Resources/assets/admin/index.js'))
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

// Shop config

Encore
  .addEntry('fiftydegsyliuscachepluigin-shop', path.resolve(__dirname, './src/Resources/assets/shop/index.js'))
  .setOutputPath('public/fifty-deg/sylius-cache-plugin/shop')
  .setPublicPath('/fifty-deg/sylius-cache-plugin/shop')
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

const fiftyDegSyliusCachePluginShop = Encore.getWebpackConfig();
fiftyDegSyliusCachePluginShop.name = 'fiftyDegSyliusCachePluginShop';

Encore.reset();

module.exports = [fiftyDegSyliusCachePluginAdmin, fiftyDegSyliusCachePluginShop];
