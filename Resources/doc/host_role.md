Using the host role
===================

The host role allow you to add automatically the roles in the Security Identity Manager depending by the host name.

## Installation

### Enable the host role

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    host_role:
        enabled: true
```

Now you must configure the host role in your security firewall like:

```yaml
# config/packages/security.yaml
security:
    firewalls:
        main:
            # ...
            host_roles:
                '*.domain.*': 'ROLE_WEBSITE'
                '*': 'ROLE_PUBLIC'
```

You only have to do is create your entities and set its permissions.
