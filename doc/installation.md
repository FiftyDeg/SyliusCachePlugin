<a href="../README.md" target="_blank">Back</a>

## Installation

1. Install with Composer
```bash
composer require fifty-deg/sylius-cache-plugin
```

2. Add `FiftyDeg\SyliusCachePlugin\FiftyDegSyliusCachePlugin::class => ['all' => true],` into `/config/bundles.php`

3. Register routes and vendor settings by adding the following code snippet in `config/routes.yaml`:  
```yaml
fifty_deg_sylius_cache_plugin:
    resource: "@FiftyDegSyliusCachePlugin/Resources/config/routes.yaml"
```

4. Import Webpack settings from `vendors/fifty-deg/sylius-cache-plugin/webpack.config.js` in your `webpack.config.js` file. Please, note that `fiftyDegSyliusCachePluginAdmin` is required while is optionalm see <a href="./usage.md" target="_blank">Usage</a> docs for more informations.
```js
const [
    fiftyDegSyliusCachePluginAdmin, 
    fiftyDegSyliusCachePluginShop
] = require('vendors/fifty-deg/sylius-cache-plugin/webpack.config.js');

// ...


module.exports = [
  // ...
  fiftyDegSyliusCachePluginAdmin,
  fiftyDegSyliusCachePluginShop,
];
```

---

<a href="./usage.md" target="_blank">Next: Usage</a>
