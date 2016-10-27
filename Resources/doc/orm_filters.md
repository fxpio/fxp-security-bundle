Using Doctrine ORM Filters
==========================

Build a Doctrine SQL filter automatically based on the Sonatra ACL Rules. This filter is
used to filter the result of the query and to make sure that the values of the object of which
the user does not have access to are removed from the result. The filter also makes sure that fields 
for which the user has no access to are overwritten with empty values, this is done by retrieving the
current values in the database before persisting.

> This feature requires `doctrine/orm` as a dependency.

### Configure your applications config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_security:
    doctrine:
        orm:
            object_filter_voter:   true # Enable the filter voter for ORM Object
            listener:
                acl_filter_fields: true # For clean value of object fields on post load and restore value on presist defined by ACLs
            filter:
                rule_filters:      true # Load the default ORM Filters of ACL Rules

doctrine:
    orm:
        entity_managers:
            default:
                filters:
                    sonatra_acl:
                        # Enable the Doctrine SQL Filter for Sonatra Rule Filters
                        class:   Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Filter\AclFilter
                        enabled: true
```

### Regarding the AclVoter

It is used indirectly by Doctrine ORM. It is not used by the AclFilter,
but by the Doctrine event `PreLoad` (used in Doctrine UnitOfWorks). The
listener will get the list of objects retrieved by Doctrine, preload the
ACLs for all retrieved entities, and checked with the AclManager (so
with the Voter) if each field is authorized in READ.

### Regarding the ACL Filter Fields

The cleaning of the fields is performed in the `Unit of Work` of
Doctrine via the listeners `postLoad` and `onFlush`, see the source files
[AclListener](https://github.com/sonatra/SonatraSecurityBundle/blob/master/Doctrine/ORM/Listener/AclListener.php)
and [AclObjectFilter](https://github.com/sonatra/SonatraSecurityBundle/blob/master/Acl/Domain/AclObjectFilter.php) 
for more information.

### Regarding the RuleDefinition and RuleFilterDefinition

- `RuleDefinition` is used by the `AclVoter`
- `RuleFilterDefinition` is used by the `AclFilter` for Doctrine ORM

If the `RuleDefinition` is created, a `RuleFilterDefinition` must also
be created (with the same name).

### Regarding the ORM Pagination

They are not in the result of Doctrine query. In the same way that if
you add a selection criteria to your ORM request (that's what doing the
AclFilter).

**Example:**

You have 1000 entities, A user only has permission to access 500 entities (1 of 2),
the user makes a request with a pagination with 50, you will have:

**Request size:** 50
**Total size:** 500
**Page number:** 1
**Total page:** 10

> The fields of the objects of the ORM Query will be cleaned, if the
user does not have access to these fields in reading.
