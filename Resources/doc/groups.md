Using the groups
================

Add the users and/or organizations in groups and allow the usage of groups in the
Symfony Security Authorization Checker.

## Installation

### Step 1: Create the group model

Run this command to create the Group entity:

```
$ php bin/console make:entity

 Class name of the entity to create or update (e.g. BraveGnome):
 > Group

 created: src/Entity/Group.php
 created: src/Repository/GroupRepository.php

 Entity generated! Now let's add some fields!
 You can always add more fields later manually or by re-running this command.

 New property name (press <return> to stop adding fields):
 >
```

To make your Group entity compatible with this bundle, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\GroupInterface` and the trait `Fxp\Component\Security\Model\Traits\GroupTrait` like:

```php
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\Traits\GroupTrait;

class Group implements GroupInterface
{
    use GroupTrait;

    // ...
}
```

### Step 2: Enable the group for the security checker

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    security_voter:
        group: true # Enable to check the group in the Symfony Security Authorization Checker
```

```yaml
# config/packages/doctrine.yaml``
doctrine:
    # ...
    orm:
        resolve_target_entities:
            Fxp\Component\Security\Model\GroupInterface: App\Entity\Group # the FQCN of your group entity
```

### Step 3: Add the groups in the user model

To make your User entity compatible with the groups, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\Traits\EditGroupableInterface` and the trait
`Fxp\Component\Security\Model\Traits\EditGroupableTrait` like:

```php
use Fxp\Component\Security\Model\Traits\EditGroupableInterface;
use Fxp\Component\Security\Model\Traits\EditGroupableTrait;

class User implements UserInterface, EditGroupableInterface
{
    use EditGroupableTrait;

    // ...
}
```

### Step 4: Add the groups in the organization user model (optional)

To make your Organization User entity compatible with the groups, you must update the entity by implementing the interface
`Fxp\Component\Security\Model\Traits\EditGroupableInterface` and the trait
`Fxp\Component\Security\Model\Traits\EditGroupableTrait` like:

```php
use Fxp\Component\Security\Model\Traits\EditGroupableInterface;
use Fxp\Component\Security\Model\Traits\EditGroupableTrait;

class OrganizationUser implements OrganizationUserInterface, EditGroupableInterface
{
    use EditGroupableTrait;

    // ...
}
```

### Step 5: Update the Doctrine schema

Also, make sure to make and run a migration for the new entities:

```
$ php bin/console make:migration
$ php bin/console doctrine:migrations:migrate
```
