Using Roles with Sonatra SecurityBundle
=======================================

SecurityBundle allows you manage Roles directly into the database,
including the role hierarchy.

This example requires `doctrine/orm` as a dependency.

### Create the Role class

``` php
// src/Acme/CoreBundle/Entity/Role.php

namespace Acme\CoreBundle\Entity;

use Sonatra\Component\Security\Model\Role as BaseRole;

class Role extends BaseRole
{
}
```

### Create the Role mapping

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

    <entity name="Acme\CoreBundle\Entity\Role" table="core_role">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>

        <many-to-many field="parents" target-entity="Role" mappedBy="children" />

        <many-to-many field="children" target-entity="Role" inversedBy="parents">
            <join-table name="core_roles_children">
                <join-columns>
                    <join-column name="role_id" referenced-column-name="id" />
                </join-columns>
                <inverse-join-columns>
                    <join-column name="children_role_id" referenced-column-name="id" />
                </inverse-join-columns>
            </join-table>
        </many-to-many>

    </entity>
</doctrine-mapping>
```

### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    role_class:                     Acme\CoreBundle\Entity\Role
    role_hierarchy:
        enabled:                    true # Enable the role hierarchy for organizational context
        cache:                      null # Defined the service cache for role hierarchy (optional)
    acl:
        access_voter:
            role_security_identity: true # Override the Symfony Role Hierarchy Voter (default true)
    doctrine:
        orm:
            listener:
                role_hierarchy:     true # Enable the Doctrine ORM listener of role hierarchy
```

### Update your database schema

```bash
$ php app/console doctrine:schema:update --force
```
