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
use FOS\UserBundle\Model\GroupInterface as FosGroupInterface;
use FOS\UserBundle\Model\User;
use Sonatra\Bundle\SecurityBundle\Model\Traits\RoleableTrait;

/**
 * This is the domain class for the Organization User object.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class OrganizationUser implements OrganizationUserInterface
{
    use RoleableTrait;

    /**
     * @var OrganizationInterface
     */
    protected $organization;

    /**
     * @var UserInterface|null
     */
    protected $user;

    /**
     * @var string|null
     */
    protected $invitationEmail;

    /**
     * @var string|null
     */
    protected $invitationToken;

    /**
     * @var Collection|null
     */
    protected $groups;

    /**
     * Constructor.
     *
     * @param OrganizationInterface $organization The organization
     * @param UserInterface         $user         The user
     */
    public function __construct(OrganizationInterface $organization, UserInterface $user)
    {
        $this->organization = $organization;
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
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
    public function setInvitationEmail($email)
    {
        $this->invitationEmail = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitationEmail()
    {
        return $this->invitationEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function setInvitationToken($token)
    {
        $this->invitationToken = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitationToken()
    {
        return $this->invitationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function isInvitation()
    {
        return null !== $this->invitationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = User::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Gets the groups granted to the user.
     *
     * @return GroupInterface[]|Collection
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
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(FosGroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(FosGroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAdmin()
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $name = null !== $this->user ? $this->user->getUsername() : $this->invitationEmail;

        return $this->organization->getName().':'.$name;
    }
}
