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
 * This is the domain class for the Role object.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class Role implements RoleInterface, \Serializable
{
    protected $id;
    protected $name;
    protected $parents;
    protected $children;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Get id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole()
    {
        return $this->name;
    }

    /**
     * Gets the role name.
     *
     * @return string the role name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the role name.
     *
     * @param string $name
     *
     * @return Role the current object
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets a parent on the current role.
     *
     * @param Role $role
     */
    abstract public function addParent($role);

    /**
     * Gets all parent.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    abstract public function getParents();

    /**
     * Sets a child on the current role.
     *
     * @param Role $role
     */
    abstract public function addChild($role);

    /**
     * Gets all children.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    abstract public function getChildren();

    /**
     * Get the children roles.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRoles()
    {
        return $this->getChildren();
    }

    /**
     * Serializes the content of the current Role objects.
     *
     * @return string
     */
    public function serialize()
    {
        return json_encode(array($this->name, $this->id));
    }

    /**
     * Unserializes the given string in the current Role object.
     *
     * @param serialized
     */
    public function unserialize($serialized)
    {
        list($this->name, $this->id) = json_decode($serialized);
    }

    /**
     * Convert the role to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRole();
    }

    /**
     * Clone the role.
     */
    public function __clone()
    {
        $this->id = null;
    }
}
