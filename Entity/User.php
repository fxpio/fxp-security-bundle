<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use FOS\UserBundle\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 *
 * @ORM\MappedSuperclass
 *
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="username",
 *         column=@ORM\Column(type="string", length=255)
 *     ),
 *     @ORM\AttributeOverride(name="usernameCanonical",
 *         column=@ORM\Column(type="string", length=255, unique=true)
 *     ),
 *     @ORM\AttributeOverride(name="email",
 *         column=@ORM\Column(type="string", length=255)
 *     ),
 *     @ORM\AttributeOverride(name="emailCanonical",
 *         column=@ORM\Column(type="string", length=255, unique=true)
 *     ),
 *     @ORM\AttributeOverride(name="enabled",
 *         column=@ORM\Column(type="boolean")
 *     ),
 *     @ORM\AttributeOverride(name="salt",
 *         column=@ORM\Column(type="string")
 *     ),
 *     @ORM\AttributeOverride(name="salt",
 *         column=@ORM\Column(type="string")
 *     ),
 *     @ORM\AttributeOverride(name="password",
 *         column=@ORM\Column(type="string")
 *     ),
 *     @ORM\AttributeOverride(name="lastLogin",
 *         column=@ORM\Column(type="datetime", nullable=true)
 *     ),
 *     @ORM\AttributeOverride(name="confirmationToken",
 *         column=@ORM\Column(type="string", nullable=true)
 *     ),
 *     @ORM\AttributeOverride(name="passwordRequestedAt",
 *         column=@ORM\Column(type="datetime", nullable=true)
 *     ),
 *     @ORM\AttributeOverride(name="locked",
 *         column=@ORM\Column(type="boolean")
 *     ),
 *     @ORM\AttributeOverride(name="expired",
 *         column=@ORM\Column(type="boolean")
 *     ),
 *     @ORM\AttributeOverride(name="expiresAt",
 *         column=@ORM\Column(type="datetime", nullable=true)
 *     ),
 *     @ORM\AttributeOverride(name="credentialsExpired",
 *         column=@ORM\Column(type="boolean")
 *     ),
 *     @ORM\AttributeOverride(name="credentialsExpireAt",
 *         column=@ORM\Column(type="datetime", nullable=true)
 *     )
 * })
 */
abstract class User extends BaseUser
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function addRole($role)
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        $roles = array();

        foreach ($this->roles->toArray() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * Get the list of entity role.
     *
     * @return array The list of Role instance
     */
    public function getEntityRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role)
    {
        return $this->roles->contains($role);
    }

    /**
     * {@inheritDoc}
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroups()
    {
        return $this->groups->toArray();
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function hasGroup($name)
    {
        return $this->groups->contains($name);
    }

    /**
     * {@inheritDoc}
     */
    public function addGroup(GroupInterface $group)
    {
        $this->groups->add($group);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }
}
