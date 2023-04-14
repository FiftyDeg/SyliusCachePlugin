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

## Usage

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

## Customization

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


## Development

For a comprehensive guide on Sylius Plugins development please go to Sylius documentation,
there you will find the <a href="https://docs.sylius.com/en/latest/plugin-development-guide/index.html">Plugin Development Guide</a>, that is full of examples.

## Quickstart Installation

### Traditional

1. Run `composer create-project sylius/plugin-skeleton ProjectName`.

2. From the plugin skeleton root directory, run the following commands:

    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn build)
    $ (cd tests/Application && APP_ENV=test bin/console assets:install public)
    
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:database:create)
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)
    ```

To be able to set up a plugin's database, remember to configure you database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

### Docker

- Launch the docker image via `./.docker/bin/start.sh` command
- Once the image is ready, run fixtures etc. through one of the following commands
  - `./.docker/bin/make_dev.sh` for dev environment
  - `./.docker/bin/make_test.sh` for test environment
- open `localhost/en_US/`

## Testing

  - Enter the docker image `docker exec -it sylius-cache-plugin_app_1 sh`

  - PHPUnit

    ```bash
    APP_ENV=test vendor/bin/phpunit
    ```

  - PHPSpec

    ```bash
    APP_ENV=test vendor/bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    APP_ENV=test vendor/bin/behat --strict --tags="~@javascript"
    ```

  - Behat (JS scenarios)
 
    1. [Install Symfony CLI command](https://symfony.com/download).
 
    2. Start Headless Chrome:
    
      ```bash
      google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
      ```
    
    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:
    
      ```bash
      symfony server:ca:install
      APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
      ```
    
    4. Run Behat:
    
      ```bash
      vendor/bin/behat --strict --tags="@javascript"
      ```
    
  - Static Analysis
  
    - Psalm
    
      ```bash
      vendor/bin/psalm
      ```
      
    - PHPStan
    
      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

  - Coding Standard
  
    ```bash
    vendor/bin/ecs check
    ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```
    
- Using `dev` environment:

    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
