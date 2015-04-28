ACL Manipulator
===============

## Usage

```php
// ...
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

$user = $this->get('security.token_storage')->getToken()->getUser();
$comment = new Comment(); // create some entity
// ... do work on entity

// entity must be persisted and flushed before AclManipulator can act on it (needs identifier)
$em->persist($comment);
$em->flush();

$aclManipulator = $this->get('sonatra_security.acl.manipulator');

// Add owner object permission
$aclManipulator->addObjectPermission($user, $comment, MaskBuilder::MASK_OWNER);

// Same with class permissions:
$aclManipulator->addClassPermission($user, $comment, MaskBuilder::MASK_OWNER);

// Same with class field permissions:
$aclManipulator->addClassFieldPermission($user, $comment, 'title', MaskBuilder::MASK_OWNER);

$aclManipulator->deleteAclFor($comment);
$em->remove($comment);
$em->flush();
```

You can use the mask constant or integer of mask, or the string action (not case sensitive),
but also the array of mask action.

```php
// ...
// add read, create, edit permissions for a class 'My\\Entity\\Class\\Name'
$aclManipulator->addClassPermission($user, 'My\\Entity\\Class\\Name', array(MaskBuilder::MASK_VIEW, 2, 'edit'));

// ...
```
