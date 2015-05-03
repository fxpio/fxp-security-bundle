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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Organization interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationInterface
{
    /**
     * Get the id of model.
     *
     * @return int
     */
    public function getId();

    /**
     * Set the name.
     *
     * @param string $name The name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the user of organization.
     *
     * @param UserInterface|null $user The user of organization
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
     * Check if the organization is a dedicated organization for the user.
     *
     * @return bool
     */
    public function isUserOrganization();

    /**
     * Get the roles of organization.
     *
     * @return ArrayCollection
     */
    public function getRoles();

    /**
     * Get the role names of organization.
     *
     * @return string[]
     */
    public function getRoleNames();

    /**
     * Check the presence of role in organization.
     *
     * @param string $role The role name
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Add a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return self
     */
    public function addRole(RoleInterface $role);

    /**
     * Remove a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return self
     */
    public function removeRole(RoleInterface $role);

    /**
     * Get the groups of organization.
     *
     * @return ArrayCollection
     */
    public function getGroups();

    /**
     * Get the group names of organization.
     *
     * @return string[]
     */
    public function getGroupNames();

    /**
     * Check the presence of group in organization.
     *
     * @param string $group The group name
     *
     * @return bool
     */
    public function hasGroup($group);

    /**
     * Add a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function addGroup(GroupInterface $group);

    /**
     * Remove a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function removeGroup(GroupInterface $group);

    /**
     * Get the users of organization.
     *
     * @return ArrayCollection
     */
    public function getOrganizationUsers();

    /**
     * Get the usernames of organization.
     *
     * @return string[]
     */
    public function getOrganizationUserNames();

    /**
     * Check the presence of username in organization.
     *
     * @param string $username The username
     *
     * @return bool
     */
    public function hasOrganizationUser($username);

    /**
     * Add a organization user in organization.
     *
     * @param OrganizationUserInterface $organizationUser The organization user
     *
     * @return self
     */
    public function addOrganizationUser(OrganizationUserInterface $organizationUser);

    /**
     * Remove a organization user in organization.
     *
     * @param OrganizationUserInterface $organizationUser The organization user
     *
     * @return self
     */
    public function removeOrganizationUser(OrganizationUserInterface $organizationUser);

    /**
     * @return string
     */
    public function __toString();
}
