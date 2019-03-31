Using the object filter
=======================

The object filter allow you to empty the record field value if the current user has not the permission.

Also, if the current user change the record field value while he does not have the permissions, the object filter will
restore automatically the previous value when the entity will save.

## Installation

### Enable the object filter

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    object_filter:
        enabled: true
```

### Enable the object filter for Doctrine

By activating the listener Doctrine of object filter, it will restore the previous saved values of record fields
if the current user has not the permissions.

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    doctrine:
        orm:
            object_filter_voter: true # Enable the Doctrine ORM Collection Object Filter
            listeners:
                object_filter: true # Enable the Doctrine ORM Object Filter Listener
```
