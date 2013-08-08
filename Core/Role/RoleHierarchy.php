<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role;

use Sonatra\Bundle\SecurityBundle\Core\Role\Cache\CacheInterface;

use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Sonatra\Bundle\SecurityBundle\Event\ReachableRoleEvent;
use Sonatra\Bundle\SecurityBundle\Events;
use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var string
     */
    private $roleClassname;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param array    $hierarchy     An array defining the hierarchy
     * @param Registry $registry
     * @param string   $roleClassName
     */
    public function __construct(array $hierarchy, RegistryInterface $registry, $roleClassname, CacheInterface $cache)
    {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cacheExec = array();
        $this->cache = $cache;
    }

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param RoleInterface[] $roles An array of RoleInterface instances
     *
     * @return RoleInterface[] An array of RoleInterface instances
     */
    public function getReachableRoles(array $roles)
    {
        if (0 === count($roles)) {
            return $roles;
        }

        $rolenames = array();

        foreach ($roles as $role) {
            if (!is_string($role) && !($role instanceof RoleInterface)) {
                $roleClass = 'Symfony\Component\Security\Core\Role\RoleInterface';

                throw new SecurityException(sprintf('The Role class must be an instance of "%s"', $roleClass));
            }

            $rolenames[] = ($role instanceof RoleInterface) ? $role->getRole() : $role;
        }

        $id = sha1(implode('|', $rolenames));

        // find the hierarchy in excecution cache
        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        // find the hierarchy in cache
        $reachableRoles = $this->cache->read($id);

        if (null !== $reachableRoles) {
            return $reachableRoles;
        }

        // build hierarchy
        $reachableRoles = parent::getReachableRoles($roles);
        $em = $this->registry->getManagerForClass($this->roleClassname);
        $repo = $em->getRepository($this->roleClassname);
        $entityRoles = array();
        $filterIsEnabled = $em->getFilters()->isEnabled('sonatra_acl');

        if ($filterIsEnabled) {
            $em->getFilters()->disable('sonatra_acl');
        }

        if (null !== $this->eventDispatcher) {
            $event = new ReachableRoleEvent($reachableRoles);
            $event = $this->eventDispatcher->dispatch(Events::PRE_REACHABLE_ROLES, $event);
            $reachableRoles = $event->geReachableRoles();
        }

        if (count($rolenames) > 0) {
            $entityRoles = $repo->findBy(array('name' => $rolenames));
        }

        foreach ($entityRoles as $eRole) {
            $reachableRoles = array_merge($reachableRoles, $this->getReachableRoles($eRole->getChildren()->toArray()));
        }

        // cleaning double
        $existingRoles = array();
        $finalRoles = array();

        foreach ($reachableRoles as $index => $role) {
            if (!in_array($role->getRole(), $existingRoles)) {
                if (!($role instanceof Role)) {
                    $role = new Role($role->getRole());
                }

                $existingRoles[] = $role->getRole();
                $finalRoles[] = $role;
            }
        }

        // insert in cache
        $this->cache->write($id, $finalRoles);
        $this->cacheExec[$id] = $finalRoles;

        if (null !== $this->eventDispatcher) {
            $event->setRreachableRoles($finalRoles);
            $event = $this->eventDispatcher->dispatch(Events::POST_REACHABLE_ROLES, $event);
            $finalRoles = $event->geReachableRoles();
        }

        if ($filterIsEnabled) {
            $em->getFilters()->enable('sonatra_acl');
        }

        return $finalRoles;
    }
}
