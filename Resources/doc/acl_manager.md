Using ACL Manager
=================

ACL Manager is used by the ACL voter to check the authorizations in
Symfony Authorization Checker, or in the Doctrine ORM Filter.

All the ACL rules are defined in the ACL Manager.

ACL Manager allow to preload the Symfony ACL Entries to drastically
optimize the performance. It uses the batch system of Symfony ACL to load
all of the ACEs of objects and object fields.

## Usage

### Preload the ACLs

If you'll be doing work on a lot of entities at the same time, use AclManager#preloadAcls():

```php
$products = $repo->findAll();

$aclManager = $this->get('sonatra_security.acl.manager');
$aclManager->preloadAcls($products);

// ...
```
