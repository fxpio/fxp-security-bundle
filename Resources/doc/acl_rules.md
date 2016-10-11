Using ACL Rules
===============

ACL Rules allow to build the sharing rules over the Symfony ACLs. It can
be used to design simple or complex sharing rules.

This bundle uses tables configured by Symfony SecurityBundle: therefore
no change.

Keep in mind that the more you add rules, more the ORM request is
complex, and therefore costly to time.

You can applied the following rules on `class`, `entity`, `class field`, `entity field`:

- `disabled` for disable the rule
- `allow` for always allow the access
- `deny` for always deny the access
- `class` for get the access with the rule defined for the class (applied for all entities)
- `object` for get the access with the rule defined for the entity
- `parent` use the parent rule
- `affirmative` for get the access with the rule defined for the class `OR` the entity
- `unanimous` for get the access with the rule defined for the class `AND` the entity

- `disabled` always authorized (acl disabled, skip symfony find ACLs)
- `allow` always authorized (skip symfony find ACLs)
- `deny` always unauthorized (skip symfony find ACLs)
- `class` search only the OID for the class in ACLs, so if no OID found for the class, access is unauthorized
- `entity` search only the OID for the entity in ACLs, so if no OID found for the entity, access is unauthorized
- `affirmative` search the OID for the entity and class in the ACLs, so if a Entity OID is found but no Class OID is found, access is authorized (ditto for the reverse). But if no OID found, access is unauthorized
- `unanimous` search the OID for the entity and class in the ACLs, si if a Entity OID is found but no Class OID is found, access is unauthorized (ditto for the reverse). We need a Entity OID and Class OID

**Explanation:**

- If the rule is `object`, you must imperatively have a `OID` for each `entity` and each `SID`
- If the rule is `class`, you must imperatively have a `OID` for the `class` and each `SID`, the `OID` for each `entity` is not used
- If the rule is `affirmative`, you must have a `OID` for the `class` **OR** each `entity` and each `SID`
- If the rule is `unanimous`, you must have a `OID` for the `class` **AND** each `entity` and each `SID`

If no `OID` is found for a given `SID`, then the `SID` has no access permissions.

> **Note:**
>
> - `OID`: it's the Object Identity
> - `SID`: it's the Security Identity: `user`, `role`, `group`, `organization`

**Example of configuration:**
```yaml
sonatra_security:
    acl:
        default_rule: 'disabled'
        rules:
            'Acme\DemoBundle\Entity\User':
                fields:
                    password:
                        rules:
                            VIEW:          'deny'
                            EDIT:          'allow'
                            CREATE:        'allow'

            'Acme\DemoBundle\Entity\Blog':
                default:                   'affirmative' # override the 'default_rule'
                rules:
                    VIEW:                  'allow'       # override the default of this class
                    EDIT:                  'class'       # override the default of this class
                    DELETE:                'affirmative' # override the default of this class
                    UNDELETE:              'unanimous'   # override the default of this class
                default_fields:            'affirmative' # override the default of this class for all fields
                fields:
                    id:                    'allow' # defined VIEW, EDIT, DELETE, etc... with 'allow'
                    name:
                        default:           'class' # override the default
                        rules:
                            VIEW:          'allow'  # override the default
                            EDIT:          'allow' # override the default
                            DELETE:        'unanimous'
                    icon:                  'allow'

            'Acme\DemoBundle\Entity\Post': 'class' # override the 'default_rule' and defined 'class' rule for All Mask of the class and the fields
```
