Getting Started
===============

## Prerequisites

This version of the bundle requires Symfony 3.3+.

This example requires `friendsofsymfony/user-bundle` as a dependency in
a [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
project.

## Installation

1. Download and install FOS UserBundle
2. Download the bundle using composer
3. Enable the bundle
4. Update your user model
5. Create the role model
6. Create the permission model
7. Configure your application's config.yml
8. Configure and initialize the permissions

### Step 1: Download and install FOS UserBundle

Follow the installation instructions in the [official documentation of Symfony]
(https://symfony.com/doc/master/bundles/FOSUserBundle/index.html).

### Step 2: Download the bundle using composer

Add Sonatra SecurityBundle in your composer.json:

```
$ composer require sonatra/security-bundle:"^1.0.0" sonatra/doctrine-extensions:"^1.0.0"
```

Composer will install the bundle to your project's `vendor/sonatra` directory.

> **Note:**
>
> Because this example enable all features, you must add the dependency
> `sonatra/doctrine-extensions` to use role hierarchy and sharing features.

### Step 3: Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sonatra\Bundle\SecurityBundle\SonatraSecurityBundle(),
    );
}
```

### Step 4: Update your user model

Add the `Sonatra\Component\Security\Model\UserInterface` into your group model:

```php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Sonatra\Component\Security\Model\UserInterface;

class User extends BaseUser implements UserInterface
{
    //...
}
```

### Step 5: Create the role model

#### Create the role class

``` php
// src/AppBundle/Entity/Role.php

namespace AppBundle\Entity;

use Sonatra\Component\Security\Model\Role as BaseRole;

class Role extends BaseRole
{
}
```

#### Create the role mapping

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\Role" table="core_role">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <many-to-many field="parents" target-entity="Role" mapped-by="children" />

        <many-to-many field="children" target-entity="Role" inversed-by="parents">
            <join-table name="core_role_children">
                <join-columns>
                    <join-column name="role_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="children_role_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>

        <many-to-many field="permissions" target-entity="Permission" inversed-by="roles">
            <join-table name="core_role_permission">
                <join-columns>
                    <join-column name="role_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="permission_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>

    </entity>
</doctrine-mapping>
```

### Step 6: Create the permission model

#### Create the permission class

``` php
// src/AppBundle/Entity/Permission.php

namespace AppBundle\Entity;

use Sonatra\Component\Security\Model\Permission as BasePermission;

class Permission extends BasePermission
{
}
```

#### Create the permission mapping

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\Permission" table="core_permission">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <many-to-many field="roles" mapped-by="permissions" target-entity="Role"/>

        <many-to-many field="sharingEntries" mapped-by="permissions" target-entity="Sharing"/>
    </entity>
</doctrine-mapping>
```

### Step 7: Create the sharing model

#### Create the sharing class

``` php
// src/AppBundle/Entity/Sharing.php

namespace AppBundle\Entity;

use Sonatra\Component\Security\Model\Sharing as BaseSharing;

class Sharing extends BaseSharing
{
}
```

#### Create the sharing mapping

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

    <entity name="AppBundle\Entity\Sharing" table="core_sharing">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <field name="subjectId" column="subject_id" type="integer" />

        <field name="identityId" column="identity_id" type="integer" />

        <many-to-many field="permissions" target-entity="Permission" inversed-by="sharingEntries">
            <join-table name="core_sharing_permission">
                <join-columns>
                    <join-column name="sharing_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="permission_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>
    </entity>
</doctrine-mapping>
```

### Step 8: Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    role_class:                     AppBundle\Entity\Role
    permission_class:               AppBundle\Entity\Permission
    sharing_class:                  AppBundle\Entity\Sharing
    object_filter:
        enabled:                    true # Enable the object filter (optional)
    role_hierarchy:
        enabled:                    true # Enable the role hierarchy for organizational context (optional)
        cache:                      null # Defined the service cache for role hierarchy (optional)
    security_voter:
        role_security_identity:     true # Override the Symfony Role Hierarchy Voter (optional)
    sharing:
        enabled:                    true # (optional)
        identity_types:
            AppBundle\Entity\User:
                roleable:           true # (optional)
            AppBundle\Entity\Role:
                permissible:        true # (optional)
    doctrine:
        orm:
            object_filter_voter:    true # Enable the Doctrine ORM Collection Object Filter (optional)
            listeners:
                permission_checker: true # Enable the Doctrine ORM Permission Checker Listener (optional)
                object_filter:      true # Enable the Doctrine ORM Object Filter Listener(optional)
                role_hierarchy:     true # Enable the Doctrine ORM listener of role hierarchy (optional)
            filters:
                sharing:            true # Enable the Doctrine ORM SQL Filter for sharing the entities (optional)
doctrine:
    orm:
        entity_managers:
            default:
                filters:
                    sonatra_sharing:     # Enable the SQL Filter for sharing (optional)
                        class:      Sonatra\Component\Security\Doctrine\ORM\Filter\SharingFilter
                        enabled:    true
```

> **Note:**
>
> If you use the role hierarchy or sharing, you must add the optional dependency `sonatra/doctrine-extensions`

### Step 9: Configure and initialize the permissions

#### Update your database schema

```bash
$ php bin/console doctrine:schema:update --force
```

### Next Steps

You can override the default configuration adding `sonatra_security` tree in `app/config/config.yml`.
To get an overview off all the available Sonatra Security configuration options, execute the command:

```bash
$ php bin/console config:dump-reference SonatraSecurityBundle
```

Now that you have completed the basic installation and configuration of the
Sonatra SecurityBundle, you are ready to learn more about using this bundle.

The following documents are available:

- [Using Groups with Sonatra SecurityBundle](groups.md)
- [Using Organizations](organizations.md)
