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

use FOS\UserBundle\Model\GroupableInterface;

/**
 * Organization user interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationUserInterface extends GroupableInterface
{
    /**
     * Set the organization.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return self
     */
    public function setOrganization($organization);

    /**
     * Get the organization.
     *
     * @return OrganizationInterface
     */
    public function getOrganization();

    /**
     * Set the user of organization.
     *
     * @param UserInterface $user The user of organization
     *
     * @return self
     */
    public function setUser($user);

    /**
     * Get the user of organization.
     *
     * @return UserInterface
     */
    public function getUser();

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
     * @return RoleInterface[] The user roles
     */
    public function getRoles();

    /**
     * Check if the organization user is an admin (contain the ROLE_ADMIN).
     *
     * @return bool
     */
    public function isAdmin();

    /**
     * @return string
     */
    public function __toString();
}
