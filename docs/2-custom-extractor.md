Custom extractor
=======================

A typical case may be that the requesting party does not make the request with an idempotent key.
Or the key is sent in two or more different fields. 

Or, perhaps, you only want to **cached responses to a certain resource**.

If you need a more specific way to extract the key, you can create a custom exctractor, 
inheriting from the `AbstractExtractor` class, and adding in the configuration the `extractor` option as follows:
```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    extractor: 'App\Extractor\MyCustomExtractor::class'
```
And the class `MyCustomExtractor::class` as follow:
```php
<?php
declare(strict_types=1);

namespace App\Extractor;

use Conejerock\IdempotencyBundle\Extractor\AbstractExtractor;
use Symfony\Component\HttpFoundation\Request;

class MyCustomExtractor extends AbstractExtractor
{
    public function extract(Request $request): ?string
    {
       return $request->getMethod()."-".$request->query->get('resourceId');
    }
}
```
