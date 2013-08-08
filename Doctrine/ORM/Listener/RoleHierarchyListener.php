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
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Sonatra\Bundle\SecurityBundle\Core\Role\Cache\CacheInterface;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\GroupInterface;

/**
 * Invalidate the role hierarchy cache when users, roles or groups is inserted,
 * updated or deleted.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchyListener implements EventSubscriber
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(Events::onFlush);
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $scheduledInsertions = $uow->getScheduledEntityInsertions($uow);
        $scheduledUpdates = $uow->getScheduledEntityUpdates($uow);
        $scheduledDeletations = $uow->getScheduledEntityDeletions($uow);
        $invalidate = false;

        // check all scheduled insertions
        foreach ($scheduledInsertions as $object) {
            if ($invalidate) {
                break;
            }

            $invalidate = $this->invalidateCache($object);
        }

        // check all scheduled updates
        foreach ($scheduledUpdates as $object) {
            if ($invalidate) {
                break;
            }

            $invalidate = $this->invalidateCache($object);
        }

        // check all scheduled deletations
        foreach ($scheduledDeletations as $object) {
            if ($invalidate) {
                break;
            }

            $invalidate = $this->invalidateCache($object);
        }

        if ($invalidate) {
            $this->cache->flush();
        }
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param object $object
     *
     * @return boolean
     */
    protected function invalidateCache($object)
    {
        if ($object instanceof UserInterface
                || $object instanceof RoleHierarchisableInterface
                || $object instanceof GroupInterface) {
            return true;
        }

        return false;
    }
}
