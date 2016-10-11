Using Commands
==============

**You have these commands:**

- `security:acl:add` Add a specified right from a given identifier on a given domain (class or object)
- `security:acl:info` Gets the rights for a specified class and identifier, and optionally for a given field
- `security:acl:remove` Remove a specified right from a given identifier on a given domain (class or object).
- `security:group:create` Create a group
- `security:group:delete` Delete a group
- `security:group:demote` Demote a group by removing a role
- `security:group:info` Security infos of group
- `security:group:promote` Promotes a group by adding a role
- `security:host:info` Security infos of host role
- `security:role:child:add` Add child role
- `security:role:child:remove` Remove child role
- `security:role:create` Create a role
- `security:role:delete` Delete a role
- `security:role:info` Security infos of role
- `security:role:parent:add` Add parent role
- `security:role:parent:remove` Remove parent role
- `security:user:create` Create a user. (fos:user:create alias)
- `security:user:degrouping` Remove a group in user
- `security:user:delete` Delete a user
- `security:user:demote` Demote a user by removing a role (fos:user:demote alias)
- `security:user:grouping` Add a group in user
- `security:user:info` Security infos of user
- `security:user:promote` Promotes a user by adding a role (fos:user:promote alias)

**Command example:**
```bash
$ php app/console security:acl:info role ROLE_ADMIN AcmeDemoBundle:Blog --domainid=1 -c
```
```bash
$ php app/console security:acl:info user user.name AcmeDemoBundle:Blog --domainid=1 -c
```
```bash
$ php app/console security:acl:info group direction AcmeDemoBundle:Blog --domainid=1 -c
```

If you do not use the option `-c`, you will only get what is recorded in the tables of the ACLs.
