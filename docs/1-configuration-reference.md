Configure reference
=========
The package extracts the **idempotent key** with the path `location` which is in a certain `scope`.
By default, the `scope` can be of three types: **body, headers and query**.

### Body Scope

If the `scope: body`, the object specified in the `location` path will be searched _recursively in body_.

```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    location: 'this.field.nested.idempotentKey'
    scope: 'body'
```
And request is:

```http
POST /hello-world HTTP/1.1

Host: localhost:80
Connection: Keep-Alive

{
    "this": {
        "field": {
            "nested": {
                "idempotentKey": "clqdy4j5k0000kf51987hmuao"
            }
        }
    },
    "other_values": []
}
```
The key to be extracted is `clqdy4j5k0000kf51987hmuao`

### Query Scope

If the `scope: query`, the object specified in the `location` path will be a _queryParam's name_.

```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    location: 'idempotentKey'
    scope: 'query'
```
And request is:

```http
DELETE /hello-world?idempotentKey=clqdy4j5k0000kf51987hmuao HTTP/1.1

Host: localhost:80
Connection: Keep-Alive
```
The key to be extracted is `clqdy4j5k0000kf51987hmuao`


### Headers Scope

If the `scope: headers`, the object specified in the `location` path will be a _header name_.

```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    location: 'header-idempotency-key'
    scope: 'headers'
```
And request is:

```http
PUT /hello-world HTTP/1.1

Host: localhost:80
Connection: Keep-Alive
Header-Idempotency-Key: clqdy4j5k0000kf51987hmuao
```
The key to be extracted is `clqdy4j5k0000kf51987hmuao`

## Mandatory behaviour
If the `mandatory: true`, and not exists **location** in **scope**, throws `IdempotentKeyIsMandatoryException::class`
```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    location: 'header-idempotency-key'
    scope: 'headers'
    mandatory: true
```
And request is:
```http
PUT /hello-world HTTP/1.1

Host: localhost:80
Connection: Keep-Alive
```
Then, **response** will be failed with status 500


## Endpoints
If the `endpoints` option is empty, it will be applied to all paths in the project. If it is set, it will be applied only to the paths specified in this option.
```yaml
# config/packages/idempotency.yaml
idempotency:
    name: api
    endpoints: ['/webhook/idempotent-endpoint']
    scope: 'body'
    location: 'event.object.response.idempotent_key'
    mandatory: true
```
