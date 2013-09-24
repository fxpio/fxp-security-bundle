Getting Started With Sonatra SecurityBundle
===========================================

## Prerequisites

This version of the bundle requires Symfony 2.3+.

## Installation

Installation is a quick, 4 step process:

1. Download Sonatra SecurityBundle using composer
2. Enable the bundle
3. Create your Role class
4. Configure the bundle

### Step 1: Download Sonatra SecurityBundle using composer

Add Sonatra SecurityBundle in your composer.json:

``` js
{
    "require": {
        "sonatra/security-bundle": "~1.0"
    }
}
```

Or tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update sonatra/security-bundle
```

Composer will install the bundle to your project's `vendor/sonatra` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sonatra\Bundle\SecurityBundle\SonatraSecurityBundle(),
    );
}
```

### Step 3: Create your Role class

#### Create the Role class

``` php
// src/Acme/CoreBundle/Entity/Role.php
<?php

namespace Acme\CoreBundle\Entity;

use Sonatra\Bundle\SecurityBundle\Model\Role as BaseRole;

class Role extends BaseRole
{
}
```

#### Create the Role mapping

``` xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

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

### Step 4: Configure the bundle

You can override the default configuration adding `sonatra_security` tree in `app/config/config.yml`.
For see the reference of Sonatra Security Configuration, execute command:

``` bash
$ php app/console config:dump-reference SonatraSecurityBundle 
```

If you haven't configured the ACL enable it in `app/config/security.yml`:

``` yaml
# app/config/security.yml
security:
    acl:
        connection: default
```

Finally run the ACL init command

    php app/console init:acl

### Next Steps

Now that you have completed the basic installation and configuration of the
Sonatra SecurityBundle, you are ready to learn about usages of the bundle.

The following documents are available:

- [ACL Manipulator](acl_manipulator.md)
- [ACL Manager](acl_manager.md)
