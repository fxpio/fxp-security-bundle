Fxp Security Bundle
===================

[![Latest Version](https://img.shields.io/packagist/v/fxp/security-bundle.svg)](https://packagist.org/packages/fxp/security-bundle)
[![Build Status](https://img.shields.io/travis/fxpio/fxp-security-bundle/master.svg)](https://travis-ci.org/fxpio/fxp-security-bundle)
[![Coverage Status](https://img.shields.io/coveralls/fxpio/fxp-security-bundle/master.svg)](https://coveralls.io/r/fxpio/fxp-security-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fxpio/fxp-security-bundle/master.svg)](https://scrutinizer-ci.com/g/fxpio/fxp-security-bundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/74707490-7a7f-4dd8-91c9-84af5de547a1.svg)](https://insight.sensiolabs.com/projects/74707490-7a7f-4dd8-91c9-84af5de547a1)

The Fxp SecurityBundle is a Extended Role-Based Access Control (E-RBAC) including the management of roles,
role hierarchy, groups, and permissions with a granularity ranging from global permission to permission for
each field of each object. With the sharing rules, it's possible to define users, groups, roles or permissions
for each record of an object. In this way, a user can get more permissions due to the context defined by the
sharing rule.

Features include:

- All features of [Fxp Security](https://github.com/fxpio/fxp-security)
- Configurator of all Fxp Security features
- Override the security access control config to allow to use custom expression language
  functions defined with the tag `security.expression_language_provider` in `allow_if` option
  (expressions are compiled on cache compilation)
- Compiler pass to inject service dependencies of custom expression function providers in
  variables of expression voter
- `@Security` annotation compatible with custom expression functions and variables
- Security factory for host role
- Compiler pass for object filter voters

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

[LICENSE](LICENSE)

About
-----

Fxp SecurityBundle is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/fxpio/fxp-security-bundle/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/fxpio/fxp-security-bundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
