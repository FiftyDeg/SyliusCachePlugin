<a href="../README.md" target="_blank">Back</a>

## Important notes:
When using the template event / block cache, please, avoid caching those templates that are using CSRF tokens like the add to cart button, contact form, newsletter optin form and so on.

## Usage
  
### Configure cache behaviour
Create the `config/packages/fifty_deg_sylius_cache.yaml` file (you can also create it per environment) in order to configure the template events and block cache behaviour. Sample configuration below: 
```yaml
fifty_deg_sylius_cache:
  is_cache_enabled: true
  template_events:
    - name: sylius.shop.layout.header
      block_default_ttl: 86400
      blocks:
        - name: header
          ttl: 3600
    - name: sylius.shop.layout.footer
      ttl: 86400
```

- `ttl` param is measured in seconds
- The `is_cache_enabled` value is a feature flag to quickly enable / disable cache (mainly for testing purposes).
- If you specify block level cache, the template event cache `ttl` parameter will be ignored.
- You can specify the default block cache ttl through the `block_default_ttl` parameter and, later, adjust cache ttl for specific blocks within the same template event.

### Use cache on the twig side
You can apply template event cache also on the twig side by adding a third parameter (the `ttl`) when invoking the `sylius_template_event` function, e.g.:

```twig
{{ sylius_template_event('sylius.shop.layout.footer', [], 86400) }}
```

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
            // Return cached HTML
            return new Response($cacheValue);
        }

        $twigData = [
            "name" => "John Doe"
        ];

        $rendered = $this->templatingEngine->render(
                '@MyAwesomeBundle/_userName.html.twig',
                $twigData
            );

        // Cache the rendered HTML and return it
        $this->cacheAdapter->set($cacheKey, $rendered, CacheAdapterInterface::TTL_ONE_HOUR);

        return new Response($rendered);
    }
}
```

### Lazy load dynamic content
You can lazy load dynamic content in a cached template by including the `fiftyDegSyliusCachePluginShop` webpack configuration in your `webpack.config.js` file (see <a href="./installation.md" target="_blank">Installation</a>).

For example, if you want to cache the whole header and keep the dynamic behaviour of the shopping cart button, you can:

### Flush cache
You can flush the entire ecosystem cache via `php bin/console c:c` or you can flush just Fifty Deg cache by visiting `https://domain.com/admin/fiftydeg-cache/index/`  

```twig
<div
    id="sylius-cart-button"
    class="ui circular cart button"
    {# IMPORTANT: if your channels are hosted in subdomains, use path instead of url twig extension in order to prevent CORS errors when switching channels since this template is cached #}
    data-lazy-load-content-url="{{ path('route_to_your_lazy_load_controller') }}"
>
    <!-- Add your facade here, content will be replaced dynamically -->
</div>
```
---

<a href="./customization.md" target="_blank">Next: Customization</a>
