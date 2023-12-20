Getting started
=========

Installation
----

Add [conejerock/idempotency-bundle](https://packagist.org/packages/conejerock/idempotency-bundle) to your ``composer.json`` file:

```terminal
> composer require "conejerock/idempotency-bundle"
```
### Register the bundle

Register bundle into `config/bundles.php` (Flex did it automatically):

```php

   return [
       //...
       Conejerock\IdempotencyBundle\IdempotencyBundle::class => ['all' => true],
   ];

```

Configuration
-------------
Configure file `idempotency.yaml` in `config/packages` adding:
```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    methods: ['POST', 'PUT', 'DELETE'] //by default
    location: 'header-idempotency-key'
    scope: 'headers'
```

Further documentation
---------------

The following documents are available:
* [1. Configuration reference](./1-configuration-reference.md)
* [2. Custom extractor](./2-custom-extractor.md)
