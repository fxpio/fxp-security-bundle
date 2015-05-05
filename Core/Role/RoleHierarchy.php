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

use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Sonatra\Bundle\SecurityBundle\ReachableRoleEvents;
use Sonatra\Component\Cache\Adapter\CacheInterface;
use Sonatra\Component\Cache\CacheElement;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Sonatra\Bundle\SecurityBundle\Event\ReachableRoleEvent;
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
     * @param array             $hierarchy     An array defining the hierarchy
     * @param RegistryInterface $registry
     * @param string            $roleClassname
     * @param CacheInterface    $cache
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
     *
     * @throws SecurityException When the role class is not an instance of '\Symfony\Component\Security\Core\Role\RoleInterface'
     */
    public function getReachableRoles(array $roles)
    {
        if (0 === count($roles)) {
            return $roles;
        }

        $roleNames = array();
        $nRoles = array();

        foreach ($roles as $role) {
            if (!is_string($role) && !($role instanceof RoleInterface)) {
                $roleClass = 'Symfony\Component\Security\Core\Role\RoleInterface';

                throw new SecurityException(sprintf('The Role class must be an instance of "%s"', $roleClass));
            }

            $roleNames[] = ($role instanceof RoleInterface) ? $role->getRole() : $role;
            $nRoles[] = ($role instanceof RoleInterface) ? $role : new Role((string) $role);
        }

        $roles = $nRoles;
        $id = $this->getUniqueId($roleNames);

        // find the hierarchy in execution cache
        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        // find the hierarchy in cache
        $element = $this->cache->get($id);
        $reachableRoles = $element->getData();

        if (!$element->isExpired() && null !== $reachableRoles) {
            return $reachableRoles;
        }

        // build hierarchy
        $reachableRoles = parent::getReachableRoles($roles);
        $em = $this->registry->getManagerForClass($this->roleClassname);
        $repo = $em->getRepository($this->roleClassname);
        $entityRoles = array();
        /* @var ReachableRoleEvent $event */
        $event = null;

        if (null !== $this->eventDispatcher) {
            $event = new ReachableRoleEvent();
            $event->setReachableRoles($reachableRoles);
            $event = $this->eventDispatcher->dispatch(ReachableRoleEvents::PRE, $event);
            $reachableRoles = $event->geReachableRoles();
        }

        if (count($roleNames) > 0) {
            $entityRoles = $repo->findBy(array('name' => $roleNames));
        }

        /* @var RoleHierarchisableInterface $eRole */
        foreach ($entityRoles as $eRole) {
            $reachableRoles = array_merge($reachableRoles, $this->getReachableRoles($eRole->getChildren()->toArray()));
        }

        // cleaning double
        $existingRoles = array();
        $finalRoles = array();

        foreach ($reachableRoles as $role) {
            if (!in_array($role->getRole(), $existingRoles)) {
                if (!($role instanceof Role)) {
                    $role = new Role($role->getRole());
                }

                $existingRoles[] = $role->getRole();
                $finalRoles[] = $role;
            }
        }

        // insert in cache
        $this->cache->set($id, $finalRoles, CacheElement::YEAR);
        $this->cacheExec[$id] = $finalRoles;

        if (null !== $this->eventDispatcher) {
            $event->setReachableRoles($finalRoles);
            $event = $this->eventDispatcher->dispatch(ReachableRoleEvents::POST, $event);
            $finalRoles = $event->geReachableRoles();
        }

        return $finalRoles;
    }

    /**
     * Get the unique id.
     *
     * @param array $roleNames The role names
     *
     * @return string
     */
    protected function getUniqueId(array $roleNames)
    {
        return sha1(implode('|', $roleNames));
    }
}
