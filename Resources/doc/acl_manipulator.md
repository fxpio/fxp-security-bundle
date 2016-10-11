Using ACL Manipulator
=====================

ACL Manipulator is a helper to manipulating simply the Symfony ACLs.

You are not forced to used the Sonatra ACL Manipulator, in fact, it's
just a helper to simplify the manipulation of ACL of Symfony Security.

You can apply an ACL rules on:

- user (contains roles and groups)
- role (contains other roles)
- group (contains roles)
- organization (contains roles)

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
