Using the anonymous role
========================

The anonymous role allow you to add automatically the `ROLE_ANONYMOUS` in the Security Identity Manager. In this way,
you can create a Role `ROLE_ANONYMOUS` and set all necessary permissions for anonymous users.

## Installation

### Enable the anonymous role

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    anonymous_role:
        enabled: true
```

Now you must enable the anonymous role in your security firewall like:

```yaml
# config/packages/security.yaml
security:
    firewalls:
        main:
            # ...
            anonymous_role: true
```

You only have to do is create your `ROLE_ANONYMOUS` entity and set its permissions.

### Change the anonymous role name

You can change the anonymous role name in the security firewall:

```yaml
# config/packages/security.yaml
security:
    firewalls:
        main:
            # ...
            anonymous_role: 'ROLE_CUSTOM_ANONYMOUS'
```
