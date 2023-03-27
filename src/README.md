# Install

### Step 1: Enable the plugin inside bundles.php

In `config/bundles.php` add

``` php
    FiftyDeg\SyliusCachePlugin\FiftyDegSyliusCachePlugin::class => ['all' => true]
```

### Step 2: Register routes and vendor settings
In order to register routes, add the following code snippet in `config/routes.yaml`:
```yaml
fiftydeg_sylius_cache_plugin:
    resource: "@FiftyDegSyliusCachePlugin/Resources/config/routes.yaml"
```
In `config/services.yaml` remove this bundle from autowiring and register vendor settings:

```yaml
services:
    App\:
        resource: '../src/*'
        exclude: '../src/{FiftyDeg/Cache,Entity,Migrations,Tests,Kernel.php}'
```

```yaml
imports:
    - { resource: "@FiftyDegSyliusCachePlugin/Resources/config/config_vendor.yaml" }
```

### Step 3: Setup webpack encore assets

Add to global `webpack.config.js`:

```js
Encore.reset();

const [fiftyDegCacheAdmin] = require('./src/FiftyDeg/Cache/webpack.config');

// ...
module.exports = [
  fiftyDegCacheAdmin,
];
```
  
### Step 4: PHPStan
If you're using PHPStan, please, consider adding the following rules in `<project_root>/phpstan.neon` in order to prevent dependency injection validation errors:
```yaml
parameters:
    ignoreErrors:
    - '#Call to an undefined method Symfony\\Component\\Config\\Definition\\Builder\\NodeParentInterface::scalarNode\(\).#'
    - '#Call to an undefined method Symfony\\Component\\Config\\Definition\\Builder\\NodeParentInterface::arrayNode\(\).#'
```

# Usage

### Setup cache behaviour
Create the `config/packages/fiftydeg_cache.yaml` file (you can also create it per environment) in order to configure cache settings.
Available settings are:
- `is_cache_enabled` (boolean) Defines if cache is enabled, or not.
- `cacheable_sylius_template_events`: (array of objects) allows you to define the sylius templates events that can be cached, and the cache TTL
  - Each entry contains the `name` and `ttl` params
  
Below, a sample configuration:  
```yaml
fifty_deg_sylius_cache:
    is_cache_enabled: true
    cacheable_sylius_template_events:
        # Layout cache
        - { name: 'sylius.shop.layout.header', ttl: 600 }
        - { name: 'sylius.shop.layout.header.navigation', ttl: 86400 }
        - { name: 'sylius.shop.layout.header.widgets', ttl: 86400 }
        - { name: 'sylius.shop.layout.header.search', ttl: 86400 }
        - { name: 'sylius.shop.layout.footer', ttl: 86400 }
        - { name: 'sylius.shop.layout.after_content', ttl: 86400 }
```

### Atomically specify the sylius template event cache on the Twig side
The `sylius_template_event` twig extension now has a third parameter, the cache TTL.  
This settings will take precedence over the `yaml` configuration.   
You can set it like below:  
  
```twig
{{ sylius_template_event('sylius.maintenance.layout.javascripts', [], 3600) }}
```  
  
It's suggested to use the yaml configuration file (see *Setup cache behaviour*) since it could be difficult to debug and identify `sylius_template_event` affected by cache.  
  
### Use cache on the PHP side
You can use this bundle also on the PHP side by simply injecting the service and handling your cache logic, like in the example below:  
```php
<?php

declare(strict_types=1);

namespace App\MyNamespace;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class CachedController
{
    public function __construct(
        private Environment $templatingEngine,
        private CacheAdapterInterface $cacheAdapter
    ) { }

    public function showAction(Request $request): Response
    {
        // If needed, prepare a per locale and host (aka channel) cache key
        $locale     = $request->get('_locale');
        $host       = $request->getHost();

        $cacheKey   = self::class . "__showAction__{$locale}__{$host}";
        $cacheValue = $this->cacheAdapter->get($cacheKey);

        if (!is_null($cacheValue)) {
            return new Response($cacheValue);
        }

        $twigData = [
            "name" => "John Doe"
        ];

        $rendered = $this->templatingEngine->render(
                '@MyAwesomeBundle/_userName.html.twig',
                $twigData
            );

        // Cache the rendered HTML
        $this->cacheAdapter->set($cacheKey, $rendered, 3600);

        return new Response($rendered);
    }
}

```

### Flush cache
You can flush the entire ecosystem cache via `php bin/console c:c` or you can flush just Fifty Deg cache by visiting `https://it.letshelter.local/admin/fiftydeg-cache/index/`  

# Customization

### Cache Adapter
By default, this bundle make use of the filesystem for the caching storage.  
You can implement your own cache adapter (e.g. APCu, Redis, Memcache, ...) by implementing the `FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapter` interface and replacing the `app.fifty_deg.cache.adapters.cache_adapter` service.

# TODO
- [IMPORTANT][TO BE DELETED] Inject Interface instead of Class
- Allow caching per channel
- Allow caching per sylius template block
- Add capability to register a decache method
- Add Admin translations (see also flushCache.js)
- Improve Admin UI
- Improve serialization of data containing closures
- Cache also array of eventNames in src/FiftyDeg/Cache/Twig/TemplateEventExtension.php
- Introduce and improve LazyLoadContent.js (see existing file)
