Using permissions
=================

The permissions are defined globally, and afterwards, you can associate the permissions
with each role. There are 3 types of permissions:

- `global` permissions (without class, without field), ex. `send-emails`
- `class` permissions (with class, without field), ex. `view`, `create`, `update`, `delete`, `undelete`
- `field` permissions (with class, with field), ex. `read`, `edit`

## Create the permissions

To edit the permissions, you can use directly the object instance of permission like
any doctrine entity, that is you can use the Symfony Form, Symfony Validator, and Doctrine
to create, update or delete the permissions.

Whether you edit permissions directly with the model or with Symfony Form, you must defined
this fields:

- operation (required, the name of permission, ex: `view`, `create`, `update`, `delete`, `undelete`, `read`, `edit`, `send-emails`, etc...)
- class (optional, the FQCN)
- field (optional, the property name in class)

### Examples

**Add global permission:**

```php
use AppBundle\Entity\Permission;

$perm = (new Permission())
    ->setOperation('send-emails')
;

$em->persist($perm);
$em->flush();
```

**Add class permission:**

```php
use AppBundle\Entity\Permission;
use AppBundle\Entity\Post;

$perm = (new Permission())
    ->setOperation('view')
    ->setClass(Post::class)
;

$em->persist($perm);
$em->flush();
```

**Add field permission:**

```php
use AppBundle\Entity\Permission;
use AppBundle\Entity\Post;

$perm = (new Permission())
    ->setOperation('read')
    ->setClass(Post::class)
    ->setField('title')
;

$em->persist($perm);
$em->flush();
```

### Hard config of permissions

It isn't required to define the permissions for the fields of a class, You can look the
[configuration](https://github.com/sonatra/sonatra-security-bundle/blob/master/DependencyInjection/Configuration.php)
for sections `default_permissions`, `permissions` and `permissions.fields`, it's very flexible
to limit the manual configuration.

In the case you are use the object filter service (clean the value in object with null value
or empty collection), the permissions `read` and `edit` are used instead of `view` and `update`.
You can define a mapping permission to map automatically the permission `read` for the field,
with the `view` permission on class, and the permission `edit` for the field, with the `update`
permission on class.

So, when you check the permission `read` on the field of class, the checker will check the
permission `view` on the class.

You can also define permissions directly in the configuration, which can be useful for "system"
fields, like `id`, `createdAt`, `updatedAt`, etc...

**Config example:**

```yaml
sonatra_security:
    default_permissions:
        fields:
            id:               [read]
            createdAt:        [read]
            updatedAt:        [read]
        master_mapping_permissions:
            view:             read
            update:           edit
            create:           edit
            delete:           edit
    permissions:
        AppBundle\Entity\User:
            operations:       [view, create, update, delete]
            fields:
                username:     [read, edit]
                email:        [read, edit]
                roles:        [read]
        AppBundle\Entity\Post:
            fields:
                title:        ~
                body:         ~
                commentCount: [read]
        AppBundle\Entity\Comment:
            master:           post
            operations:       [view, create, update, delete]
            fields:
                email:        [read, edit]
                message:      [read, edit]
```

For this example, the fields `title` and `body` of `Post` class, are ungranted by default
for all roles, and the field `commentCount` are only readable for all roles. So, all
permissions defined in configuration are availables and granted for all roles.

The `master_mapping_permissions` and `master` work like the fields of a class, but it
allows to check the autormizations of an entity associated instead of the field of the class.
So, when you check the autorization `edit` for the `email` field of `Comment` class,
the checker checks the autorization `update` of the `Post` class.

Of course, this behavior works for all roles defined by the hierarchy of all roles of current user.

> **Note:**
>
> It may be useful to create a data loader (with the doctrine data fixtures)
> for all permissions, launched with a command, and create 3 forms to associate
> each role with the permissions according to the 3 cases of use (global, class, field).

## Attach the permissions on the role

It's not necessary to have a specific manager to manage the permissions,
use directly the model of the roles:

```php
use AppBundle\Entity\Post;

$permissionView = $permissionRepository->findOneBy(array('operation' => 'view', 'class' => Post::class, 'field' => null));
$adminRole = $roleRepository->findOneByName('ROLE_ADMIN');

$adminRole->addPermission($permissionView);

$em->persist($adminRole);
$em->flush();
```

You can also use directly the doctrine collection of permissions in roles if you wish it.

```php
$adminRole->getRoles()->add($permissionView);
```

## Check the authorizations defined by permissions

This library work with [Symfony Security](http://symfony.com/doc/current/security.html) and you
can used the same services to validate the authorizations (see the
[Securing services doc](http://symfony.com/doc/current/security/securing_services.html) for more
details).

Consequently, this library work with the Symfony Authorization Checker, using the
prefix `perm_` before the permission name.

**Check the authorization on the object:**

```php
$this->get('security.authorization_checker')->isGranted('perm_update', $entity);
$this->get('security.authorization_checker')->isGranted('perm_update', PostInterface::class);
```

**Check the authorization on the field of object:**

```php
use Sonatra\Component\Security\Permission\FieldVote;

$this->get('security.authorization_checker')->isGranted('perm_edit', new FieldVote($entity, 'title'));
$this->get('security.authorization_checker')->isGranted('perm_edit', new FieldVote(PostInterface::class, 'title'));
```

If you prefer to use the annotations, you can use the `@Security` annotation in your controllers:

```php
use Sonatra\Bundle\SecurityBundle\Configuration\Security;

MyController {
    /**
     * @Security("is_granted('perm_view', 'AppBundle\\Entity\\Post')")
     */
    public function getPostsAction()
    {
        //...
    }

    /**
     * @Security("is_granted('perm_view', post)
     */
    public function getPostAction(PostInterface $post)
    {
        //...
    }
}
```

## What is the contexts field in permission class

The `contexts` field is not used by the permission manager or sharing manager, but it
can be used to filter the display of role permissions or sharing permissions on your edit pages.

## Why is there no manager to edit the permissions?

This library doesn't include a manager to manage permissions with roles, because it uses natively Doctrine,
and leaves you the choice to using Doctrine directly, to creating you a specific manager or to using a
resource management library (like [sonatra/resource-bundle](https://github.com/sonatra/sonatra-resource-bundle)).
