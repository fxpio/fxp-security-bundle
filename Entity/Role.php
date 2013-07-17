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

use Sonatra\Bundle\SecurityBundle\Model\Role as AbstractRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * This is the model class Doctrine-oriented for the Role object.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 *
 * @ORM\MappedSuperclass
 */
class Role extends AbstractRole
{
    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role", mappedBy="children")
     */
    protected $parents;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="parents")
     * @ORM\JoinTable(name="role_children",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="children_role_id", referencedColumnName="id")}
     *      )
     **/
    protected $children;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function addParent($role)
    {
        $this->parents->add($role);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * {@inheritDoc}
     */
    public function addChild($role)
    {
        $this->children->add($role);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren()
    {
        return $this->children;
    }
}
