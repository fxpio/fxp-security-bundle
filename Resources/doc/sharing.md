Using the sharing
=================

Given that you cannot set permissions directly on entity, you must use the SharingManager
(`fxp_security.sharing_manager`).

## Enable the sharing for a class

There is a difference between permission and sharing. The permissions tell you whether
the user, role, or group have access rights based on operations (view, create, etc...),
and if the Object Filter is enabled, the entity is cleaned at loading, leaving only
the Id (always) and the authorized field values. However, permissions do not filter the
doctrine queries. To do this, you must enable and configure the sharing.

For entities to be filtered at the time of the Doctrine query, you have the option to
build your own `SQLFilter`, Or use the `SharingFilter` class in this bundle (see the
[doc](https://github.com/fxpio/fxp-security-bundle/blob/master/Resources/doc/index.md#step-8-configure-your-applications-configyml)):

```yaml
doctrine:
    orm:
        entity_managers:
            default:
                filters:
                    fxp_sharing:
                        class:   Fxp\Component\Security\Doctrine\ORM\Filter\SharingFilter
                        enabled: true
```

This filter requires a small configuration to know what you want to do with it. By
default, the sharing for all classes is disabled (`none` value). But to filter all
records and showing only the shared records, you will use the `private` value for
all class that need a sharing, and for that, you will need to add this configuration:

```yaml
fxp_security:
    doctrine:
        orm:
            listeners:
                private_sharing: true # Enable the 'private' sharing filter type
                sharing_delete:  true # Enable the auto sharing delete when the entity is deleted
            filters:
                sharing:         true # Enable the Doctrine ORM SQL Filter for sharing the entities (optional)
    sharing:
        subjects:
            AppBundle\Entity\Post: private
```

In this way you enable the `SQLFilter` to filter any entity that is not shared with either
a user, a role, a group, or an organization.

> **Note:**
>
> You will find the values and their descriptions in the class `Fxp\Component\Security\SharingVisibilities`.

## Create the sharing entry

You can share an entity with a user, a role, a group, or an organization.

To edit the sharing entry, you can use directly the object instance of permission like
any doctrine entity, that is you can use the Symfony Form, Symfony Validator, and Doctrine
to create, update or delete the sharing entry.

Whether you edit sharing entry directly with the model or with Symfony Form, you must defined
this required fields:

- subject class (the FQCN of entity)
- subject id (the id of entity)
- identity class (the FQCN of user, role, group or organization)
- identity name (the username, role name, group name or organization name)

The sharing model works with the Subject Identities for the entities (see
`Fxp\Component\Security\Identity\SubjectIdentity`) and Security Identity for the user, role,
group, and organization (see `Fxp\Component\Security\Identity\UserSecurityIdentity`,
`Fxp\Component\Security\Identity\RoleSecurityIdentity`,
`Fxp\Component\Security\Identity\GroupSecurityIdentity` and
`Fxp\Component\Security\Identity\OrganizationSecurityIdentity`).

To retrieve the subject infos, you have these helpers:

- `SubjectIdentity::fromObject($object)`
- `SubjectIdentity::fromClassname($className)`

To retrieve the identity infos, you have these helpers:

- `UserSecurityIdentity::fromAccount($user)`
- `UserSecurityIdentity::fromToken($token)`
- `RoleSecurityIdentity::fromAccount($role)`
- `RoleSecurityIdentity::fromToken($token)`
- `GroupSecurityIdentity::fromAccount($group)`
- `GroupSecurityIdentity::fromToken($token)`
- `OrganizationSecurityIdentity::fromAccount($organization)`
- `OrganizationSecurityIdentity::fromToken($organization, $context = null, $roleHierarchy = null)`

All helpers return the instance of `Fxp\Component\Security\Identity\SecurityIdentityInterface`,
and you retrieve the type and the identifier that will must be used for the sharing instance.

It's not necessary to have a specific manager to manage the sharing entry,
use directly the model of the sharing.

### Example

**Add sharing entry:**

```php
use AppBundle\Entity\Post;
use AppBundle\Entity\Sharing;
use AppBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;

$user = $userRepository->findOneByUsername('foo.bar');
$userIdentity = UserSecurityIdentity::fromAccount($user);

$post = $postRepository->findOneBy(array('id' => 42));
$postIdentity = SubjectIdentity::fromObject($post);

$share = (new Sharing())
    ->setSubjectClass($postIdentity->getType())
    ->setSubjectId($postIdentity->getIdentifier())
    ->setIdentityClass($userIdentity->getType())
    ->setIdentityName($userIdentity->getIdentifier())
;

$em->persist($share);
$em->flush();
```

When you create a sharing entry, the identity of sharing (user, role, group or organization)
retrieves automatically the equivalent of the `view` permission of the class.

Of course, the user must have permissions on the class (and fields) to retrieve the shared entity,
and he has the authorizations via the permissions of all its roles, or the roles attached to the
sharing entry, or directly with the permissions attached to the sharing entry.

## Attach the permissions on a sharing entry

You can define permissions for each sharing entry:

```php
use AppBundle\Entity\Post;
use AppBundle\Entity\Sharing;
use AppBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;

$user = $userRepository->findOneByUsername('foo.bar');
$userIdentity = UserSecurityIdentity::fromAccount($user);

$post = $postRepository->findOneBy(array('id' => 42));
$postIdentity = SubjectIdentity::fromObject($post);

$permissionSendEmails = $permissionRepository->findOneBy(array(
    'operation' => 'send-emails',
    'class' => null,
    'field' => null,
));
$share = $shareRepository->findOneBy(array(
    'subjectClass' => $postIdentity->getType(),
    'subjectId' => $postIdentity->getIdentifier(),
    'identityClass' => $userIdentity->getType(),
    'identityName' => $userIdentity->getIdentifier(),
));

$share->addPermission($permissionSendEmails);

$em->persist($share);
$em->flush();
```

You can also use directly the doctrine collection of permissions in sharing if you wish it.

```php
$share->getPermissions()->add($permissionSendEmails);
```

## Attach the roles on a sharing entry

You can define roles for each sharing entry (user only):

```php
use AppBundle\Entity\Post;
use AppBundle\Entity\Sharing;
use AppBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;

$user = $userRepository->findOneByUsername('foo.bar');
$userIdentity = UserSecurityIdentity::fromAccount($user);

$post = $postRepository->findOneBy(array('id' => 42));
$postIdentity = SubjectIdentity::fromObject($post);

$adminRole = $roleRepository->findOneByName('ROLE_ADMIN');

$share = $shareRepository->findOneBy(array(
    'subjectClass' => $postIdentity->getType(),
    'subjectId' => $postIdentity->getIdentifier(),
    'identityClass' => $userIdentity->getType(),
    'identityName' => $userIdentity->getIdentifier(),
));

$share->addRole($adminRole->getRole());

$em->persist($share);
$em->flush();
```

You can also use directly the doctrine collection of roles in sharing if you wish it.

```php
$share->getRoles()->add($adminRole);
```

## Define an activation date

You can define the activation date to create a sharing entry but activate this sharing
only after a date.

## Define an expiration date

You can define the expiration date to create a sharing entry but disable this sharing
only after a date.

> **Note:**
>
> Even though the share entry is in the database, and the Doctrine filter selects only valid
> share entries, it is recommended that you create a CRON task to regularly delete the expired
> share entries.

## Why is there no manager to edit the sharing entry?

This library doesn't include a manager to manage sharing with entities, because it uses natively Doctrine,
and leaves you the choice to using Doctrine directly, to creating you a specific manager or to using a
resource management library (like [fxp/resource-bundle](https://github.com/fxpio/fxp-resource-bundle)).
