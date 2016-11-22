Sonatra Security Bundle
=======================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/security-bundle.svg)](https://packagist.org/packages/sonatra/security-bundle)
[![Build Status](https://img.shields.io/travis/sonatra/sonatra-security-bundle/master.svg)](https://travis-ci.org/sonatra/sonatra-security-bundle)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/sonatra-security-bundle/master.svg)](https://coveralls.io/r/sonatra/sonatra-security-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/sonatra-security-bundle/master.svg)](https://scrutinizer-ci.com/g/sonatra/sonatra-security-bundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/74707490-7a7f-4dd8-91c9-84af5de547a1.svg)](https://insight.sensiolabs.com/projects/74707490-7a7f-4dd8-91c9-84af5de547a1)

The Sonatra SecurityBundle is a Role-Based Access Control Level 2 with advanced permissions
and sharing rules.

Features include:

- All features of [Sonatra Security](https://github.com/sonatra/sonatra-security)
- Configurator of all Sonatra Security features
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

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Sonatra SecurityBundle is a [sonatra](https://github.com/sonatra) initiative.
See also the list of [contributors](https://github.com/sonatra/sonatra-security-bundle/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/sonatra/sonatra-security-bundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
