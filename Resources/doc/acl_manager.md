ACL Manager
===========

## Usage

### Preload the ACLs

If you'll be doing work on a lot of entities, use AclManager#preloadAcls():

```php
$products = $repo->findAll();

$aclManager = $this->get('sonatra_security.acl.manager');
$aclManager->preloadAcls($products);

// ...
```
