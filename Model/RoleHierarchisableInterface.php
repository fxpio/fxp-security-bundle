<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Model;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Interface for role hierarchisable.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RoleHierarchisableInterface extends RoleInterface
{
    /**
     * Add a parent on the current role.
     *
     * @param RoleInterface $role
     */
    public function addParent(RoleHierarchisableInterface $role);

    /**
     * Remove a parent on the current role.
     *
     * @param RoleInterface $role
     */
    public function removeParent(RoleHierarchisableInterface $parent);

    /**
     * Gets all parent.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getParents();

    /**
     * Gets all parent names.
     *
     * @return array
     */
    public function getParentNames();

    /**
     * Check if role has parent.
     *
     * @param string $name
     */
    public function hasParent($name);

    /**
     * Add a child on the current role.
     *
     * @param RoleInterface $role
     */
    public function addChild(RoleHierarchisableInterface $role);

    /**
     * Remove a child on the current role.
     *
     * @param RoleInterface $role
     */
    public function removeChild(RoleHierarchisableInterface $child);

    /**
     * Gets all children.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren();

    /**
     * Gets all children names.
     *
     * @return array
     */
    public function getChildrenNames();

    /**
     * Check if role has child.
     *
     * @param string $name
     */
    public function hasChild($name);
}
