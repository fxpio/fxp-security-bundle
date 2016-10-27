Using Groups with Sonatra SecurityBundle
========================================

Allow the usage of groups in the Symfony Security Authorization Checker.

### Configure the groups in FOS UserBundle

Follow the installation instructions in the [official documentation of Symfony]
(https://symfony.com/doc/master/bundles/FOSUserBundle/groups.html).

### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    group_class: Acme\CoreBundle\Entity\Group
    acl:
        access_voter:
            groupable: true # Enable to check the group in the Symfony Security Authorization Checker (default true)
```

### Update your database schema

```bash
$ php app/console doctrine:schema:update --force
```
