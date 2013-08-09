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
use Doctrine\ORM\PersistentCollection;
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
        $collection = $uow->getScheduledEntityInsertions($uow);
        $collection = array_merge($collection, $uow->getScheduledEntityUpdates($uow));
        $collection = array_merge($collection, $uow->getScheduledEntityDeletions($uow));
        $collection = array_merge($collection, $uow->getScheduledCollectionUpdates($uow));
        $collection = array_merge($collection, $uow->getScheduledCollectionDeletions($uow));
        $invalidate = false;

        // check all scheduled insertions
        foreach ($collection as $object) {
            if ($invalidate) {
                break;
            }

            $invalidate = $this->invalidateCache($uow, $object);
        }

        if ($invalidate) {
            $this->cache->flush();
        }
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param UnitOfWork $uow
     * @param object     $object
     *
     * @return boolean
     */
    protected function invalidateCache($uow, $object)
    {
        if ($object instanceof UserInterface
                || $object instanceof RoleHierarchisableInterface
                || $object instanceof GroupInterface) {
            $fields = array_keys($uow->getEntityChangeSet($object));
            $checkFields = array('roles');

            if ($object instanceof RoleHierarchisableInterface) {
                $checkFields = array_merge($checkFields, array('name'));
            }

            foreach ($fields as $field) {
                if (in_array($field, $checkFields)) {
                    return true;
                }
            }

        } elseif ($object instanceof PersistentCollection) {
            $mapping = $object->getMapping();
            $ref = new \ReflectionClass($mapping['sourceEntity']);

            if (in_array('Sonatra\\Bundle\\SecurityBundle\\Model\\RoleHierarchisableInterface', $ref->getInterfaceNames())
                    && 'children' === $mapping['fieldName']) {

                return true;
            }
        }

        return false;
    }
}
