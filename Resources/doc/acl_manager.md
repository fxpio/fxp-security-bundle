Sonatra SecurityBundle ACL Manager
==================================

## Prerequisites

[Installation and Configuration](index.md)

## Use

``` php
<?php
// ...
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

$user = $this->get('security.context')->getToken()->getUser();
$comment = new Comment(); // create some entity
// ... do work on entity

// entity must be persisted and flushed before AclManager can act on it (needs identifier)
$em->persist($comment);
$em->flush();

$aclManager = $this->get('sonatra.acl.manager');

// Add owner object permission
$aclManager->addObjectPermission($user, $comment, MaskBuilder::MASK_OWNER);

// Same with class permissions:
$aclManager->addClassPermission($user, $comment, MaskBuilder::MASK_OWNER);

$aclManager->deleteAclFor($comment);
$em->remove($comment);
$em->flush();
```

### Preload the ACLs

If you'll be doing work on a lot of entities, use AclManager#preloadAcls():

```php
<?php

$products = $repo->findAll();

$aclManager = $this->get('sonatra.acl.manager');
$aclManager->preloadAcls($products);

// ...
```

You can use the mask constant or integer of mask, or the string action (not case sensitive), but also the array of mask action.

```php
<?php
// ...
// add read, create, edit permissions for a class 'My\\Entity\\Class\\Name'
$aclManager->addClassPermission($user, 'My\\Entity\\Class\\Name', array (MaskBuilder::MASK_VIEW, 2, 'edit'));

// ...
```
