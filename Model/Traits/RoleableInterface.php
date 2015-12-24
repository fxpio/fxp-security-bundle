<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Model\Traits;

use Symfony\Component\Security\Core\Role\Role;

/**
 * Interface of roleable model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RoleableInterface
{
    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Sets the roles of the user of organization.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles);

    /**
     * Adds a role to the user of organization.
     *
     * @param string $role
     *
     * @return self
     */
    public function addRole($role);

    /**
     * Removes a role to the user of organization.
     *
     * @param string $role
     *
     * @return self
     */
    public function removeRole($role);

    /**
     * Returns the roles granted to the user of organization.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles();
}
