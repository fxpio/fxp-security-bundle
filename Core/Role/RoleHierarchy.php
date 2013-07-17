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

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;
use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchy implements RoleHierarchyInterface
{
    private $registry;
    private $roleClassname;
    private $cache = array();

    /**
     * Constructor.
     *
     * @param Registry $registry
     * @param string   $roleClassName
     */
    public function __construct(Registry $registry, $roleClassname)
    {
        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
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
                throw new SecurityException("The Role class must be an instance of 'Symfony\Component\Security\Core\Role\RoleInterface'");
            }

            $rolenames[] = is_string($role) ? $role : $role->getRole();
        }

        $cacheName = implode('__', $rolenames);

        // return the children in cache
        if (isset($this->cache[$cacheName])) {
            return $this->cache[$cacheName];
        }

        //Get all children
        $reachableRoles = array();
        $em = $this->registry->getManagerForClass($this->roleClassname);
        $repo = $em->getRepository($this->roleClassname);
        $entityRoles = array();

        $this->manageSqlFilter($em, false);

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
        $this->manageSqlFilter($em, true);

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
        $children = $role->getRoles()->toArray();
        $returnRoles[$role->getRole()] = $role;

        if (!empty($children)) {
            foreach ($children as $child) {
                $returnRoles = array_merge($returnRoles, $this->getAllChildren($child));
            }
        }

        return $returnRoles;
    }

    /**
     * Enable/Disable the ACL SQL Filter.
     *
     * @param boolean $enable
     */
    private function manageSqlFilter(EntityManager $em, $enable, $name = 'acl')
    {
        if (null === $name) {
            return;
        }

        // exception when filter is not enabled
        try {
            if ($enable) {
                $em->getFilters()->getFilter($name)->enable();

            } else {
                $em->getFilters()->getFilter($name)->disable();
            }

        } catch (\Exception $e) {
        }
    }
}
