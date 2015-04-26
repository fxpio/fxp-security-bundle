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
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * This is the domain class for the Organization object.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class Organization implements OrganizationInterface
{
    /**
     * @var int
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
    protected $roles;

    /**
     * @var Collection|null
     */
    protected $groups;

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
    public function getRoles()
    {
        return $this->roles ?: $this->roles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleNames()
    {
        $names = array();
        foreach ($this->getRoles() as $role) {
            $names[] = $role->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoleNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(RoleInterface $role)
    {
        if (!$this->isUserOrganization()
            && !$this->getRoles()->contains($role)) {
            $this->getRoles()->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole(RoleInterface $role)
    {
        if ($this->getRoles()->contains($role)) {
            $this->getRoles()->removeElement($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($group)
    {
        return in_array($group, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->isUserOrganization()
            && !$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
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
