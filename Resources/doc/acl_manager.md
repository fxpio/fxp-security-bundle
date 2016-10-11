Using ACL Manager
=================

ACL Manager is using by the ACL voter to check the authorizations in
Symfony Authorization Checker, or in the Doctrine ORM Filter.

All the ACL rules are defined in the ACL Manager.

ACL Manager allow to preload the Symfony ACL Entries to drastically
optimize the performance. It use the batch system of Symfony ACL to load
entirely the ACEs of objects and object fields.

## Usage

### Preload the ACLs

If you'll be doing work on a lot of entities, use AclManager#preloadAcls():

```php
$products = $repo->findAll();

$aclManager = $this->get('sonatra_security.acl.manager');
$aclManager->preloadAcls($products);

// ...
```
