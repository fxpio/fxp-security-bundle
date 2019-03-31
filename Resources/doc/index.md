Getting Started
===============

## Installation

1. Install the bundle
2. Create or update your user model
3. Create the role model
4. Create the permission model
5. Configure your application

### Step 1: Install the bundle

In applications using [Symfony Flex](https://symfony.com/doc/current/setup/flex.html), run this command to install
the security feature before using it:

```
$ composer require fxp/security-bundle
```

### Step 2: Create your user model

If you have already created a user entity by following the [official documentation of Symfony]
(https://symfony.com/doc/current/security.html), you can skip this section, otherwise, run this command:

```
$ php bin/console make:user

 The name of the security user class (e.g. User) [User]:
 > User

 Do you want to store user data in the database (via Doctrine)? (yes/no) [yes]:
 > yes

 Enter a property name that will be the unique "display" name for the user (e.g. email, username, uuid) [email]:
 > email

 Will this app need to hash/check user passwords? Choose No if passwords are not needed or will be checked/hashed by some other system (e.g. a single sign-on server).

 Does this app need to hash/check user passwords? (yes/no) [yes]:
 > yes

 created: src/Entity/User.php
 created: src/Repository/UserRepository.php
 updated: src/Entity/User.php
 updated: config/packages/security.yaml
```

**Make the User entity compatible with this bundle**

To make your User entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\UserInterface` and the trait `Fxp\Component\Security\Model\Traits\RoleableTrait` like:

```php
use Fxp\Component\Security\Model\Traits\RoleableTrait;
use Fxp\Component\Security\Model\UserInterface;

class User implements UserInterface
{
    use RoleableTrait;

    // ...
}
```

Otherwise, you must remove the `$roles` property and the `getRoles()` and `setRoles()` methods, because this property
and methods are already defined in the `RoleableTrait` trait with the specific logic for this bundle.

### Step 3: Create the role model

Run this command to create the Role entity:

```
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. BraveGnome):
 > Role

 created: src/Entity/Role.php
 created: src/Repository/RoleRepository.php

 Entity generated! Now let's add some fields!
 You can always add more fields later manually or by re-running this command.

 New property name (press <return> to stop adding fields):
 >
```

To make your Role entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\RoleInterface` and the trait `Fxp\Component\Security\Model\Traits\RoleTrait` like:

```php
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Model\Traits\RoleTrait;

class Role implements RoleInterface
{
    use RoleTrait;

    // ...
}
```

### Step 4: Create the permission model

Run this command to create the Permission entity:

```
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. BraveGnome):
 > Permission

 created: src/Entity/Permission.php
 created: src/Repository/PermissionRepository.php

 Entity generated! Now let's add some fields!
 You can always add more fields later manually or by re-running this command.

 New property name (press <return> to stop adding fields):
 >
```

To make your Permission entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\PermissionInterface`, the trait `Fxp\Component\Security\Model\Traits\PermissionTrait` and
the Doctrine indexes/constraints like:

```php
use Doctrine\ORM\Mapping as ORM;
use Fxp\Component\Security\Model\PermissionInterface;
use Fxp\Component\Security\Model\Traits\PermissionTrait;

/**
 * ...
 *
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="idx_permission_operation", columns={"operation"}),
 *         @ORM\Index(name="idx_permission_class", columns={"class"}),
 *         @ORM\Index(name="idx_permission_field", columns={"field"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniq_permission", columns={"operation", "class", "field"})
 *     }
 * )
 */
class Permission implements PermissionInterface
{
    use PermissionTrait;

    // ...
}
```

### Step 5: Configure your application

Add the interface in Doctrine's target entities resolver:

```yaml
# config/packages/doctrine.yaml``
doctrine:
    # ...
    orm:
        resolve_target_entities:
            Fxp\Component\Security\Model\UserInterface: App\Entity\User # the FQCN of your user entity
            Fxp\Component\Security\Model\RoleInterface: App\Entity\Role # the FQCN of your role entity
            Fxp\Component\Security\Model\PermissionInterface: App\Entity\Permission # the FQCN of your permission entity
```

Also, make sure to make and run a migration for the new entities:

```
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

## Next Steps

Now that you have completed the basic installation and configuration of the
Fxp SecurityBundle, you are ready to learn more about using this bundle.

The following documents are available:

- [Using the permissions](permissions.md)
- [Using the security expressions](expressions.md)
- [Using the security annotations](annotations.md)
- [Using the role hierarchy](role_hierarchy.md)
- [Using the groups](groups.md)
- [Using the sharing entries](sharing.md)
- [Using the organizations](organizations.md)
- [Using the object filter](object_filter.md)
- [Using the anonymous role](anonymous_role.md)
- [Using the host role](host_role.md)
