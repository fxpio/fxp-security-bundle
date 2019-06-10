Using the annotations
=====================


## Using the security annotation

Now, you can use the `@Security` annotation of the dependency `sensio/framework-extra-bundle`
in your controller like:

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

MyController {
    /**
     * @Security("is_granted('perm_view', 'App\Entity\Post')")
     */
    public function getPostsAction()
    {
        //...
    }

    /**
     * @Security("is_granted('perm_update', post)
     */
    public function getPostAction(PostInterface $post)
    {
        //...
    }
}
```

> **Note:**
>
> To use the @Security annotation, you must install the dependency `sensio/framework-extra-bundle`.
>
> To use the `is_granted()` expression function, you must [enable this expression](expressions.md).


## Using the permission annotations

With the `@Permission` and `@PermissionField` annotations, you can configure the global
permissions like the configuration of the Symfony Bundles, mut directly in your models:

```php
use Fxp\Component\Security\Configuration as FxpSecurity;

/**
 * @FxpSecurity\Permission(
 *     operations={"view", "create", "update", "delete"},
 *     fields={
 *         "id": @FxpSecurity\PermissionField(operations={"read"})
 *     }
 * )
 */
class Post
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string
     *
     * @FxpSecurity\PermissionField(operations={"read", "edit"})
     */
    protected $name;

    // ...
}
```

Of course, all the configuration of the [global permissions](annotations.md) can be configured
with the annotations.

> **Note:**
>
> The `@PermissionField` annotation can be added in the `@Permission` annotation or directly in
> the PHPDoc of the property.
