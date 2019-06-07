Using the security annotations
==============================


**Important:**

To use the security annotation, you must add the dependency `sensio/framework-extra-bundle`.

## Installation

Install the dependency:

```
$ composer require sensio/framework-extra-bundle
```

## Using security annotation

Now, you can use the `@Security` annotation in your controller like:

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
> To use the `is_granted()` expression function, you must [enable this expression](expressions.md).
