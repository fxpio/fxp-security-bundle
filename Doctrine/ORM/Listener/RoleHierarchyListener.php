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
use Doctrine\ORM\UnitOfWork;
use Sonatra\Bundle\SecurityBundle\Acl\Domain\SecurityIdentityRetrievalStrategy;
use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Bundle\SecurityBundle\Model\GroupInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationUserInterface;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Sonatra\Component\Cache\Adapter\CacheInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $strategy;

    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param CacheInterface                             $cache
     * @param SecurityIdentityRetrievalStrategyInterface $strategy
     * @param OrganizationalContextInterface             $context
     */
    public function __construct(CacheInterface $cache, SecurityIdentityRetrievalStrategyInterface $strategy, OrganizationalContextInterface $context = null)
    {
        $this->cache = $cache;
        $this->strategy = $strategy;
        $this->context = $context;
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
        $collection = $this->getAllCollections($uow);
        $invalidates = array();

        // check all scheduled insertions
        foreach ($collection as $object) {
            $invalidate = $this->invalidateCache($uow, $object);

            if (is_string($invalidate)) {
                $invalidates[] = $invalidate;
            }
        }

        $this->flushCache(array_unique($invalidates));
    }

    /**
     * Flush the cache.
     *
     * @param array $invalidates The prefix must be invalidated
     */
    protected function flushCache(array $invalidates)
    {
        if ('' !== implode('', $invalidates)) {
            if (null === $this->context) {
                $this->cache->flushAll();
            } else {
                foreach ($invalidates as $invalidate) {
                    $this->cache->flushAll($invalidate);
                }
            }

            if ($this->strategy instanceof SecurityIdentityRetrievalStrategy) {
                $this->strategy->invalidateCache();
            }
        }
    }

    /**
     * Get the merged collection of all scheduled collections.
     *
     * @param UnitOfWork $uow The unit of work
     *
     * @return array
     */
    protected function getAllCollections(UnitOfWork $uow)
    {
        $collection = $uow->getScheduledEntityInsertions($uow);
        $collection = array_merge($collection, $uow->getScheduledEntityUpdates($uow));
        $collection = array_merge($collection, $uow->getScheduledEntityDeletions($uow));
        $collection = array_merge($collection, $uow->getScheduledCollectionUpdates($uow));
        $collection = array_merge($collection, $uow->getScheduledCollectionDeletions($uow));

        return $collection;
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param UnitOfWork $uow
     * @param object     $object
     *
     * @return string|false
     */
    protected function invalidateCache($uow, $object)
    {
        if ($object instanceof UserInterface
                || $object instanceof RoleHierarchisableInterface
                || $object instanceof GroupInterface
                || $object instanceof OrganizationUserInterface) {
            $fields = array_keys($uow->getEntityChangeSet($object));
            $checkFields = array('roles');

            if ($object instanceof RoleHierarchisableInterface || $object instanceof OrganizationUserInterface) {
                $checkFields = array_merge($checkFields, array('name'));
            }

            foreach ($fields as $field) {
                if (in_array($field, $checkFields)) {
                    return $this->getPrefix($object);
                }
            }
        } elseif ($object instanceof PersistentCollection
                && $this->isRequireAssociation($object->getMapping())) {
            return $this->getPrefix($object->getOwner());
        }

        return false;
    }

    /**
     * Check if the association must be flush the cache.
     *
     * @param array $mapping The mapping
     *
     * @return bool
     */
    protected function isRequireAssociation(array $mapping)
    {
        $ref = new \ReflectionClass($mapping['sourceEntity']);

        if (in_array('Sonatra\\Bundle\\SecurityBundle\\Model\\RoleHierarchisableInterface', $ref->getInterfaceNames())
                && 'children' === $mapping['fieldName']) {
            return true;
        } elseif (in_array('FOS\\UserBundle\\Model\\GroupableInterface', $ref->getInterfaceNames())
                && 'groups' === $mapping['fieldName']) {
            return true;
        }

        return false;
    }

    /**
     * Get the cache prefix key.
     *
     * @param object $object
     *
     * @return string
     */
    protected function getPrefix($object)
    {
        if (method_exists($object, 'getOrganization')) {
            $org = $object->getOrganization();

            if ($org instanceof OrganizationInterface) {
                return $org->getId().'__';
            }
        }

        return 'user__';
    }
}
