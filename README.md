Sonatra Security Bundle
=======================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/security-bundle.svg)](https://packagist.org/packages/sonatra/security-bundle)
[![Build Status](https://img.shields.io/travis/sonatra/SonatraSecurityBundle/master.svg)](https://travis-ci.org/sonatra/SonatraSecurityBundle)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/SonatraSecurityBundle/master.svg)](https://coveralls.io/r/sonatra/SonatraSecurityBundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/SonatraSecurityBundle/master.svg)](https://scrutinizer-ci.com/g/sonatra/SonatraSecurityBundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/74707490-7a7f-4dd8-91c9-84af5de547a1.svg)](https://insight.sensiolabs.com/projects/74707490-7a7f-4dd8-91c9-84af5de547a1)

The Sonatra SecurityBundle implements all functionnalities of 
[Symfony Advanced ACL Concepts](http://symfony.com/doc/current/cookbook/security/acl_advanced.html)
and adds some interesting features.


Features include:

- All features of [Sonatra Security](https://github.com/sonatra/sonatra-security)
- Ability to replace the `hasPermission()` JMS Expression (and twig function)
- Ability to add the `hasFieldPermission()` JMS Expression (and twig function)
- Ability to replace the `hasRole()` JMS Expression (and twig function)
- Ability to replace the `hasAnyRole()` JMS Expression (and twig function)
- Commands for:
 * create/delete a user
 * create/delete a role
 * create/delete a group
 * promote/demote a user
 * promote/demote a group
 * associate/disassociate a user from a group
 * add/remove child of role
 * add/remove parent of role
 * grant/revoke permissions by user, by role or by group for a defined class (or record entity)
 * display direct and indirect roles of user (host role compatible)
 * display direct and indirect roles of group (host role compatible)
 * display direct and indirect children of role (host role compatible)
 * display the permissions by user or by role for a defined class (or a record entity)
 * display the calculated permissions by user or by role for a defined class (or a record entity)

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Sonatra SecurityBundle is a [sonatra](https://github.com/sonatra) initiative.
See also the list of [contributors](https://github.com/sonatra/SonatraSecurityBundle/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/sonatra/SonatraSecurityBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
