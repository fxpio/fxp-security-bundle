Using JMS SecurityExtraBundle
=============================

If you want to use the JMS SecurityExtraBundle with this bundle, you must
disable JMS voters in the configuration.

### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
jms_security_extra:
    secure_all_services:    false
    expressions:            true
    enable_iddqd_attribute: false
    voters:
        disable_role:       true
        disable_acl:        true
```

### Sonatra JMS expressions

You can activate the Sonatra JMS expressions of this bundle:
- `has_permission`: Checks whether the token has the given permission
   for the given object
- `has_field_permission`: Checks whether the token has the given permission
   for the given field of object
- `has_role`: Checks whether the token has a certain role (override the
  JMS expression)
- `has_any_role`: Checks whether the token has any of the given roles
  (override the JMS expression)
- `has_org_role`: Checks whether the token has any of the given roles
  defined in the current organization

#### Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
sonatra_security:
    expression:
        has_permission:       true
        has_field_permission: true
        has_role:             true
        has_any_role:         true
        has_org_role:         true
```
