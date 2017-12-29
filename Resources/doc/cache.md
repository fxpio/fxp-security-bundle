Using cache with Fxp CacheBundle
====================================

Allow to use the app cache for the role hierarchy to optimize the count of query on database. 

This example requires `fxp/cache-bundle` as a dependency in
a [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
project.

### Install the Fxp CacheBundle

Follow the installation instructions in the [documentation]
(https://github.com/fxpio/fxp-cache-bundle/blob/master/Resources/doc/index.md).

### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
framework:
    cache:
        pools:
            cache.app.role_hierarchy:
                adapter:          cache.app
                default_lifetime: 31536000
                public:           true

fxp_security:
    role_hierarchy:
        enabled: true                     # Already enabled
        cache:   cache.app.role_hierarchy # The service id of cache 
```
