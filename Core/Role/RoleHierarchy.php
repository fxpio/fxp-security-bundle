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
    public function __construct(array $hierarchy, RegistryInterface $registry, $roleClassname)
    {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cache = array();
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
        $rolenames = array();

        foreach ($roles as $role) {
            if (!is_string($role) && !($role instanceof RoleInterface)) {
                $roleClass = 'Symfony\Component\Security\Core\Role\RoleInterface';

                throw new SecurityException(sprintf('The Role class must be an instance of "%s"', $roleClass));
            }

            $rolenames[] = ($role instanceof RoleInterface) ? $role->getRole() : $role;
        }

        $cacheName = implode('__', $rolenames);

        // return the children in cache
        if (isset($this->cache[$cacheName])) {
            return $this->cache[$cacheName];
        }

        //Get all children
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
            $this->eventDispatcher->dispatch(Events::PRE_REACHABLE_ROLES, $event);
        }

        if (count($rolenames) > 0) {
            $entityRoles = $repo->findBy(array('name' => $rolenames));
        }

        foreach ($entityRoles as $eRole) {
            $reachableRoles = array_merge($reachableRoles, $this->getAllChildren($eRole));
        }

        $reachableRoles = array_values($reachableRoles);

        foreach ($reachableRoles as $index => $role) {
            if (!($role instanceof Role)) {
                $reachableRoles[$index] = new Role($role->getRole());
            }
        }

        // insert in cache
        $this->cache[$cacheName] = $reachableRoles;

        if ($filterIsEnabled) {
            $em->getFilters()->enable('sonatra_acl');
        }

        if (null !== $this->eventDispatcher) {
            $event->setRreachableRoles($reachableRoles);
            $this->eventDispatcher->dispatch(Events::POST_REACHABLE_ROLES, $event);
        }

        return $reachableRoles;
    }

    /**
     * Get the children role.
     *
     * @param RoleInterface $role
     *
     * @return RoleInterface[] The list of Role
     */
    private function getAllChildren($role)
    {
        $returnRoles = array();
        $children = $role->getChildren();
        $returnRoles[$role->getRole()] = $role;

        if (!empty($children)) {
            foreach ($children as $child) {
                $returnRoles = array_merge($returnRoles, $this->getAllChildren($child));
            }
        }

        return $returnRoles;
    }
}
