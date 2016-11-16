Using Groups with Sonatra SecurityBundle
========================================

Allow the usage of groups in the Symfony Security Authorization Checker.

This example requires `friendsofsymfony/user-bundle` as a dependency in
a [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
project.

### Configure the groups in FOS UserBundle

Follow the installation instructions in the [official documentation of Symfony]
(https://symfony.com/doc/master/bundles/FOSUserBundle/groups.html).

### Update your group model

Add the `Sonatra\Component\Security\Model\GroupInterface` into your group model:

```php
// src/Acme/CoreBundle/Entity/Group.php

namespace Acme\CoreBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Sonatra\Component\Security\Model\GroupInterface;

class Group extends BaseRole implements GroupInterface
{
    //...
}
```

### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    security_voter:
        groupable: true # Enable to check the group in the Symfony Security Authorization Checker
```

### Update your database schema

```bash
$ php app/console doctrine:schema:update --force
```
