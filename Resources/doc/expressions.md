Using the security expressions
==============================

This bundle has 3 expressions functions dedicated for the security:

- `is_basic_auth`: To check if the authentication is made with the HTTP basic authentication or not
- `is_granted`: To check if the classname or the entity instance has the permission 
- `is_organization`: To check if the organization entity instance is a organization or a "personal organization"

## Installation

You can enable only the expression functions which you need:

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    expression:
        override_voter:      true
        functions:
            is_basic_auth:   true
            is_granted:      true
            is_organization: true
```

## Using the security expressions

The expressions functions can be used in the Symfony `security.access_control` configuration (with the parameter
`allow_if`) or the Fxp `@Security` annotation.

### Use the is_basic_auth function

Call simply the is_basic_auth function to check if the authentication is made with the HTTP basic authentication or not: 

```
is_basic_auth()
```

**Example:**

```yaml
# config/packages/security.yaml
security:
    # ...

    access_control:
        - { path: ^/users, allow_if: 'is_basic_auth()' }
```

### Use the is_granted function

You can use the is_granted function like:

```
is_granted("perm_<PERMSSION>")
is_granted("perm_<PERMSSION>", "<CLASS_NAME>")
is_granted("perm_<PERMSSION>", <OBJECT_INSTANCE>)
is_granted("perm_<PERMSSION>", ["<CLASS_NAME>", "<FIELD_NAME>"])
is_granted("perm_<PERMSSION>", [<OBJECT_INSTANCE>, "<FIELD_NAME>"])
```

**Example:**

```yaml
# config/packages/security.yaml
security:
    # ...

    access_control:
        - { path: '^/admin/groups', allow_if: 'is_granted("perm_manage_security")' }
        - { path: '^/admin/groups/{id}', allow_if: 'is_granted("perm_view", id)' }
```

### Use the is_organization function

You can use the is_organization function like:

```
is_organization()
```

**Example:**

```yaml
# config/packages/security.yaml
security:
    # ...

    access_control:
        - { path: '^/{organization}/posts', allow_if: 'is_organization()' }
```
