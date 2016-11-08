Getting Started
===============

## Prerequisites

This version of the bundle requires Symfony 3.0+.

## Installation

Installation is a quick, 5 step process:

1. Download and install FOS UserBundle
2. Download the bundle using composer
3. Enable the bundle
4. Update your user model
5. Configure your application's config.yml
6. Configure and initialize the Symfony ACL

### Step 1: Download and install FOS UserBundle

Follow the installation instructions in the [official documentation of Symfony]
(https://symfony.com/doc/master/bundles/FOSUserBundle/index.html).

### Step 2: Download the bundle using composer

Add Sonatra SecurityBundle in your composer.json:

```
$ composer require sonatra/security-bundle "~1.0"
```

Composer will install the bundle to your project's `vendor/sonatra` directory.

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
// src/Acme/CoreBundle/Entity/User.php

namespace Acme\CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Sonatra\Component\Security\Model\UserInterface;

class User extends BaseUser implements UserInterface
{
    //...
}
```

### Step 5: Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    user_class: Acme\CoreBundle\Entity\User
    acl:
        security_identity: true # Override the standard security identity retrieval strategy (default true)
```

### Step 6: Configure and initialize the Symfony ACL

If you haven't configured the ACL enable it in `app/config/security.yml`:

```yaml
# app/config/security.yml
security:
    acl:
        connection: default
```

Finally run the ACL init command

```bash
$ php app/console init:acl
```

### Next Steps

You can override the default configuration adding `sonatra_security` tree in `app/config/config.yml`.
To get an overview off all the available Sonatra Security configuration options, execute the command:

```bash
$ php app/console config:dump-reference SonatraSecurityBundle
```

Now that you have completed the basic installation and configuration of the
Sonatra SecurityBundle, you are ready to learn more about using this bundle.

The following documents are available:

- [Using Roles with Sonatra SecurityBundle](roles.md)
- [Using Groups with Sonatra SecurityBundle](groups.md)
- [Using JMS SecurityExtraBundle with Sonatra SecurityBundle](jms.md)
- [Using Doctrine ORM Filters](orm_filters.md)
- [Using ACL Rules](acl_rules.md)
- [Using ACL Manipulator](acl_manipulator.md)
- [Using ACL Manager](acl_manager.md)
- [Using Commands](commands.md)
