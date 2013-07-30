<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;

/**
 * Auto update authorization field of Role entity.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleAuthorizationListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
                Events::prePersist,
                Events::preUpdate,
        );
    }

    /**
     * Pre persist action.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof RoleHierarchisableInterface) {
            $this->updateAuthorizationField($object);
        }
    }

    /**
     * Pre update action.
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof RoleHierarchisableInterface) {
            $this->updateAuthorizationField($object);
        }
    }

    /**
     * Update authorization field
     *
     * @param RoleHierarchisableInterface $role
     */
    protected function updateAuthorizationField(RoleHierarchisableInterface $role)
    {
        $role->setAuthorization(true);

        if (0 === strpos($role->getRole(), 'ROLE_')) {
            $role->setAuthorization(false);
        }
    }
}
