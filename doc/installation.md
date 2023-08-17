<a href="../README.md" target="_blank">Back</a>

## Installation

1. Install with Composer
```bash
composer require fifty-deg/sylius-cache-plugin
```

2. Add `FiftyDeg\SyliusCachePlugin\FiftyDegSyliusCachePlugin::class => ['all' => true],` into `/config/bundles.php`

3. Register routes settings by adding the following code snippet in `config/routes.yaml`:  
```yaml
fifty_deg_sylius_cache_plugin:
    resource: "@FiftyDegSyliusCachePlugin/Resources/config/routes.yaml"
```

4. Register all the services related to the admin in `config/services.yaml`:  
```yaml
import:
    resource: '@FiftyDegSyliusCachePlugin/Resources/config/config.yaml'
```

5. Import Webpack settings from `./vendor/fifty-deg/sylius-cache-plugin/webpack.config` in your `webpack.config.js` file.
```js
const [
    fiftyDegSyliusCachePluginAdmin, 
    fiftyDegSyliusCachePluginShop
] = require('./vendor/fifty-deg/sylius-cache-plugin/webpack.config');

// ...


module.exports = [
  // ...
  fiftyDegSyliusCachePluginAdmin,
  fiftyDegSyliusCachePluginShop,
];
```

---

<a href="./usage.md" target="_blank">Next: Usage</a>
