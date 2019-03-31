Using Organizations
===================

Allow to associate many user in many organizations.

## Installation

### Step 1: Create the organization model

Run this command to create the Organization entity:

```
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. BraveGnome):
 > Organization

 created: src/Entity/Organization.php
 created: src/Repository/OrganizationRepository.php

 Entity generated! Now let's add some fields!
 You can always add more fields later manually or by re-running this command.

 New property name (press <return> to stop adding fields):
 >
```

### Step 2: Create the organization user model

Run this command to create the Organization User entity:

```
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. BraveGnome):
 > OrganizationUser

 created: src/Entity/OrganizationUser.php
 created: src/Repository/OrganizationUserRepository.php

 Entity generated! Now let's add some fields!
 You can always add more fields later manually or by re-running this command.

 New property name (press <return> to stop adding fields):
 >
```

### Step 3: Update the organization model

To make your Organization entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\OrganizationInterface` and the trait `Fxp\Component\Security\Model\Traits\OrganizationTrait` like:

```php
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\Traits\OrganizationTrait;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\RoleableTrait;

class Organization implements OrganizationInterface, RoleableInterface
{
    use OrganizationTrait;
    use RoleableTrait;

    // ...
}
```

> **Note:**
>
> The `RoleableInterface` interface is optional

### Step 4: Update the organization user model

To make your Organization User entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\OrganizationUserInterface` and the trait `Fxp\Component\Security\Model\Traits\OrganizationUserTrait` like:

```php
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\Traits\OrganizationUserTrait;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\RoleableTrait;

class OrganizationUser implements OrganizationUserInterface, RoleableInterface
{
    use OrganizationUserTrait;
    use RoleableTrait;

    // ...
}
```

> **Note:**
>
> The `RoleableInterface` interface is optional

### Step 5: Update the user model

Implement in the user model, the `Fxp\Component\Security\Model\Traits\UserOrganizationUsersInterface` interface and
`Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface` interface (or `OrganizationalRequiredInterface`
interface), the `Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait` trait, the
`Fxp\Component\Security\Model\Traits\UserOrganizationUsersTrait` and the Doctrine mapping like:

```php
use Fxp\Component\Security\Model\Traits\RoleableTrait;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait;
use Fxp\Component\Security\Model\Traits\UserOrganizationUsersInterface;
use Fxp\Component\Security\Model\Traits\UserOrganizationUsersTrait;
use Fxp\Component\Security\Model\UserInterface;

class User implements UserInterface
    OrganizationalOptionalInterface, // Or OrganizationalRequiredInterface
    UserOrganizationUsersInterface
{
    use RoleableTrait;
    use OrganizationalOptionalTrait; // Or OrganizationalRequiredTrait
    use UserOrganizationUsersTrait;
    
    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Organization",
     *     mappedBy="user",
     *     fetch="EAGER",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    protected $organization;

    // ...
}
```

### Step 6: Make 'organizationable' the role model (optional)

Implement in the role model, the `Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface` interface
(or `OrganizationalRequiredInterface` interface), the `Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait`
trait (or `OrganizationalRequiredTrait` interface), and the Doctrine mapping. With the organization, the unique name
of role must be overridden to include the organization, otherwise, the role name will not unique for each organization,
like:

```php
use Doctrine\ORM\Mapping as ORM;
use Fxp\Component\Security\Model\Traits\RoleableTrait;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait;
use Fxp\Component\Security\Model\Traits\UserOrganizationUsersInterface;
use Fxp\Component\Security\Model\Traits\UserOrganizationUsersTrait;
use Fxp\Component\Security\Model\UserInterface;

/**
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniq_role_organization_name", columns={"organization_id", "name"})
 *     }
 * )
 *
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="name", column=@ORM\Column(unique=false))
 * })
 */
class Role implements RoleHierarchicalInterface
    OrganizationalOptionalInterface // Or OrganizationalRequiredInterface
{
    use RoleTrait;
    use RoleHierarchicalTrait;
    use OrganizationalOptionalTrait; // Or OrganizationalRequiredTrait
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Organization",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="organizationRoles"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $organization;

    // ...
}
```

Implement in the organization model, the `Fxp\Component\Security\Model\Traits\OrganizationRolesInterface` interface,
the `Fxp\Component\Security\Model\Traits\OrganizationRolesTrait` trait, and the Doctrine mapping like:

```php
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\Traits\OrganizationRolesInterface;
use Fxp\Component\Security\Model\Traits\OrganizationRolesTrait;
use Fxp\Component\Security\Model\Traits\OrganizationTrait;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\RoleableTrait;

class Organization implements OrganizationInterface, RoleableInterface, OrganizationRolesInterface
{
    use OrganizationTrait;
    use RoleableTrait;
    use OrganizationRolesTrait;

    // ...
}
```

### Step 7: Make 'organizationable' the group model (optional)

TODO OrganizationalOptionalTrait and interface in group + organization add OrganizationGroupsTrait and interface

Implement in the group model, the `Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface` interface
(or `OrganizationalRequiredInterface` interface), the `Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait`
trait (or `OrganizationalRequiredTrait` interface), and the Doctrine mapping. With the organization, the unique name
of group must be overridden to include the organization, otherwise, the group name will not unique for each organization,
like:

```php
use Doctrine\ORM\Mapping as ORM;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\Traits\GroupTrait;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait;

/**
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniq_group_organization_name", columns={"organization_id", "name"})
 *     }
 * )
 *
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="name", column=@ORM\Column(unique=false))
 * })
 */
class Group implements GroupInterface, OrganizationalOptionalInterface
{
    use GroupTrait;
    use OrganizationalOptionalTrait; // Or OrganizationalRequiredTrait
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Organization",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="organizationGroups"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $organization;

    // ...
}
```

Implement in the organization model, the `Fxp\Component\Security\Model\Traits\OrganizationGroupsInterface` interface,
the `Fxp\Component\Security\Model\Traits\OrganizationGroupsTrait` trait, and the Doctrine mapping like:

```php
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\Traits\OrganizationGroupsInterface;
use Fxp\Component\Security\Model\Traits\OrganizationGroupsTrait;
use Fxp\Component\Security\Model\Traits\OrganizationTrait;

class Organization implements OrganizationInterface, OrganizationGroupsInterface
{
    use OrganizationTrait;
    use OrganizationGroupsTrait;

    // ...
}
```

### Step 8: Configure your application

Add the interface in Doctrine's target entities resolver:

```yaml
# config/packages/doctrine.yaml``
doctrine:
    # ...
    orm:
        resolve_target_entities:
            Fxp\Component\Security\Model\OrganizationInterface: App\Entity\Organization # the FQCN of your organization entity
            Fxp\Component\Security\Model\OrganizationUserInterface: App\Entity\OrganizationUser # the FQCN of your organization user entity
```

And enable the organizational context like:

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    organizational_context:
        enabled: true
```


Also, make sure to make and run a migration for the new entities:

```
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```

## Work with organizational context

You can get the organizational context service with `fxp_security.organizational_context`
in container service.

The organizational context allow you to define the current organization
and organization user.

## Use your custom organizational context

To use your custom organizational context, you must create a class implementing the
`Fxp\Component\Security\Organizational\OrganizationalContextInterface` interface. Of course, you can extend the
`Fxp\Component\Security\Organizational\OrganizationalContext` class.

Register your custom organization context in the container dependency, and enable the organizational context like:

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    organizational_context:
        service_id: app.custom_organizational_context
```
