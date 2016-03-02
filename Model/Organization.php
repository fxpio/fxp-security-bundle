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
use Doctrine\Common\Collections\Collection;

/**
 * This is the domain class for the Organization object.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class Organization implements OrganizationInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var UserInterface|null
     */
    protected $user;

    /**
     * @var Collection|null
     */
    protected $organizationRoles;

    /**
     * @var Collection|null
     */
    protected $organizationGroups;

    /**
     * @var Collection|null
     */
    protected $organizationUsers;

    /**
     * Constructor.
     *
     * @param string $name The unique name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserOrganization()
    {
        return null !== $this->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationRoles()
    {
        return $this->organizationRoles ?: $this->organizationRoles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationRoleNames()
    {
        $names = array();
        foreach ($this->getOrganizationRoles() as $role) {
            $names[] = $role->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationRole($role)
    {
        return in_array($role, $this->getOrganizationRoleNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationRole(RoleInterface $role)
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationRole(RoleInterface $role)
    {
        if ($this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->removeElement($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationGroups()
    {
        return $this->organizationGroups ?: $this->organizationGroups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationGroupNames()
    {
        $names = array();
        foreach ($this->getOrganizationGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationGroup($group)
    {
        return in_array($group, $this->getOrganizationGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationGroup(GroupInterface $group)
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationGroup(GroupInterface $group)
    {
        if ($this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUsers()
    {
        return $this->organizationUsers ?: $this->organizationUsers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUserNames()
    {
        $names = array();
        foreach ($this->getOrganizationUsers() as $orgUser) {
            $names[] = $orgUser->getUser()->getUsername();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationUser($username)
    {
        return in_array($username, $this->getOrganizationUserNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationUser(OrganizationUserInterface $organizationUser)
    {
        if (!$organizationUser->getOrganization()->isUserOrganization()
            && !$this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->add($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationUser(OrganizationUserInterface $organizationUser)
    {
        if ($this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->removeElement($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }
}
