Getting Started With Sonatra SecurityBundle
===========================================

## Prerequisites

This version of the bundle requires Symfony 2.3+.

## Installation

Installation is a quick, 3 step process:

1. Download Sonatra SecurityBundle using composer
2. Enable the bundle
3. Configure the bundle

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

### Step 3: Configure the bundle

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

- [ACL Manager](acl_manager.md)
