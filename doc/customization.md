<a href="../README.md" target="_blank">Back</a>

## Customization

### Configs loader
If you need to add your own logic when fetching the Cache configuration, you can replace the `fifty_deg.sylius_cache_plugin.config_loader.config_loader` service and implement `FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface.php`.

---

### Cache adapter
By default, this plugin comes with a Filesystem Cache Adapter implementation. You can add your own Cache Adapter implementation (e.g. APCu, Redis, Memcache, ...) by implementing the `FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface` and replacing the `fifty_deg.sylius_cache_plugin.adapters.cache_adapter` service.

<a href="./development.md" target="_blank">Next: Development</a>
