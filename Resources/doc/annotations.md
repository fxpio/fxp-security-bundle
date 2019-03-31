Using the security annotations
==============================


**Important:**

To use the role hierarchy, you must add the dependency `sensio/framework-extra-bundle`:

```
$ composer require sensio/framework-extra-bundle
```

## Installation

If you prefer to use the `@Security` annotation in your controllers to check the authorization on the field of object,
you must enable this feature:

```yaml
# config/packages/fxp_security.yaml
fxp_security:
    annotations:
        security: true
```

## Using security annotation

Now, you can use the `@Security` annotation in your controller like:

```php
use Fxp\Bundle\SecurityBundle\Configuration\Security;

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
> To use the `is_granted()` expression function, you must [enable this expression](expressions.md).
