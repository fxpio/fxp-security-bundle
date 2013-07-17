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

use FOS\UserBundle\Model\Group as BaseGroup;
use Sonatra\Bundle\SecurityBundle\Model\DoctrineGroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 *
 * @ORM\MappedSuperclass
 *
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="name",
 *         column=@ORM\Column(type="string", length=255, unique=true)
 *     )
 * })
 */
abstract class Group extends BaseGroup implements DoctrineGroupInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
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
}
