<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Cache Plugin</h1>

<p align="center">A cache plugin for Sylius.</p>

## Plugin documentation

### Step 1: Enable the plugin inside bundles.php

In `config/bundles.php` add

``` php
    FiftyDeg\SyliusCachePlugin\FiftyDegSyliusCachePlugin::class => ['all' => true]
```

### Step 2: Register routes and vendor settings
In order to register routes, add the following code snippet in `config/routes.yaml`:
```yaml
fifty_deg_sylius_cache_plugin:
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
    - { resource: "@FiftyDegSyliusCachePlugin/Resources/config/config_bundle.yaml" }
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

## Usage

### Setup cache behaviour
To understand the following configuration settings, consider that there are 
- `general settings`: for every events.
- `event specific settings`
- `block specific settings` (in fact, remember that an event could contain blocks)

And, essentially, for every context (general, event, block), the cache needs 2 information to work:
- `enable`: if it is enabled or not
- `ttl`: time to live, how much time the cache will be alive.

So, create the `config/packages/<environmente-where-you-are-working-in>/fiftydeg_sylius_cache_plugin.yaml` file (you can also create it per environment) in order to configure cache settings.

Available settings are:
- `is_cache_enabled` (boolean) Defines if cache is enabled, or not, in general; it is like a switch on/of of the entire cache.
- `default_event_cache_enabled` (boolean) Defines - for each event - the default value of `default_event_cache_enabled` if it is not present (see later).
- `default_event_block_cache_enabled` (boolean) Defines - for each event block - the default value of `default_event_block_cache_enabled` if it is not present (see later).
- `default_event_cache_ttl` (integer) Defines - for each event - the default value of `ttl` if it is not present (see later).
- `default_event_block_cache_ttl` (integer) Defines - for each event block - the default value of `ttl` if it is not present (see later).
- `cacheable_sylius_template_events`: (array of objects) allows you to define the sylius templates events that can be cached, and the cache TTL
  - Each entry contains
    - `name`: (string) Defines the name of the event to cache
    - `ttl`: (integer) Defines the ttl of the event cache
    - `is_cache_enabled`: (boolean) Defines if the event cache is switched on or not
    - `default_event_block_cache_enabled`: (boolean) Defines - for each event block - the default value of `default_event_block_cache_enabled` if it is not present 
    - `default_event_block_cache_ttl`: (integer) Defines - for each event block - the default value of `ttl` if it is not present (see later).
    - `blocks`: (array of objects) allows you to define the blocks contained in the current sylius templates events, that can be cached, and the cache TTL  
      - Each entry contains
        - `name`: (string) Defines the name of the event block to cache  
        - `ttl`: (integer) Defines the ttl of the event block to cache  
        - `is_cache_enabled`: (boolean) Defines if the event block is cached or not
  
  
Below, a sample configuration:  
```yaml
fifty_deg_sylius_cache:
  is_cache_enabled: true
  default_event_cache_enabled: true
  default_event_block_cache_enabled: true
  default_event_cache_ttl: 86400
  default_event_block_cache_ttl: 86400
  cacheable_sylius_template_events:
    - name: ssylius.shop.layout.header
      ttl: 86400
      is_cache_enabled: true
      default_event_block_cache_enabled: true
      default_event_block_cache_ttl: 86400
      blocks:
        - name: template_event_cache_test
          ttl: 86400
          is_cache_enabled: true
        - name: template_event_cache_test_not_cached
          ttl: 86400
          is_cache_enabled: false
        - { name: 'sylius.shop.layout.after_content', ttl: 86400 }
    - name: sylius.shop.layout.footer
      ttl: 86400
      is_cache_enabled: true
      default_event_block_cache_enabled: true
      default_event_block_cache_ttl: 86400
      blocks:
        - name: template_event_cache_test
          ttl: 86400
          is_cache_enabled: true
        - name: template_event_cache_test_not_cached
          ttl: 86400
          is_cache_enabled: false
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
You can flush the entire ecosystem cache via `php bin/console c:c` or you can flush just Fifty Deg cache by visiting `/admin/fiftydeg-cache/index/`  

## Customization

### Cache Adapter
By default, this bundle make use of the filesystem for the caching storage.  
You can implement your own cache adapter (e.g. APCu, Redis, Memcache, ...) by implementing the `FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapter` interface and replacing the `fifty_deg.sylius_cache_plugin.adapters.cache_adapter` service.

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


## Development

For a comprehensive guide on Sylius Plugins development please go to Sylius documentation,
there you will find the <a href="https://docs.sylius.com/en/latest/plugin-development-guide/index.html">Plugin Development Guide</a>, that is full of examples.

## Quickstart Installation

### Docker

1. Execute `cd ./.docker && ./bin/start_dev.sh`
2. Configure `/etc/hosts` and add the `127.0.0.1    syliusplugin.local` new entry
3. Add `FiftyDeg\SyliusCachePlugin\FiftyDegSyliusCachePlugin::class => ['all' => true],` into /config/bundles.php 
4. Open your browser and go to `https://syliusplugin.local`

## Usage

### Running plugin tests

  - Run `cd .docker && ./bin/start_test.sh` in order to start docker compose in test mode
  - Wait docker to be up and running...
  - Run `cd .docker && ./bin/php_test.sh` in order to start static analysis and Behat tests

#### BDD
A suite for BDD testing is already present; you cand find the features in /features, and the asscoiated PHP code in /Behat/Context/Ui/Shop.
It works on two hidden divs in your project footer; one should be cached and the other one not; but you can modify the test as you can wish.

In config/packages/<environment-where-you-are-working-in>, add `sylius_ui.yaml`, edit it and insert the configuration for your 
data in page; as we mentioned before, in Sylius `events` contain `blocks`, so you could add a configuration like the following:

```
sylius_ui:
    events:
        sylius.shop.layout.footer:
            blocks:
                template_event_cache_test:
                    template: "@FiftyDegSyliusCachePlugin/Test/Shop/Layout/Footer/_templateEventCacheTest.html.twig"
                    priority: 100
                template_event_cache_test_not_cached:
                    template: "@FiftyDegSyliusCachePlugin/Test/Shop/Layout/Footer/_templateEventCacheTestNotCached.html.twig"
                    priority: 100
```

where `sylius.shop.layout.footer` is the Sylius event printin the footer; and the following blocks are two custom blocks, especially created for the test of this plugin.

As you can see you have to add two more files, `_templateEventCacheTest.html.twig` and `_templateEventCacheTestNotCached.html.twig`.
The first one is for checking the cache is working, the second one for the opposite reason.

Lastly, you have to add `config/packages/<environmente-where-you-are-working-in>/fiftydeg_sylius_cache_plugin.yaml`, as described before; so you can turn on and off che cache for the two divs in thw twigs specified before.

Please, notice that the `blocks` name are the same specified in the `sylius_ui.yaml` file.

With this template in mind, you can add or edit any test you wish; and watch it working directly in a page.
