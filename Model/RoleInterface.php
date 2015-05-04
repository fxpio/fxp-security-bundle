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

use Symfony\Component\Security\Core\Role\RoleInterface as BaseRoleInterface;

/**
 * Interface for role hierarchisable.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RoleInterface extends BaseRoleInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Gets the role name.
     *
     * @return string the role name
     */
    public function getName();

    /**
     * Sets the role name.
     *
     * @param string $name
     *
     * @return Role the current object
     */
    public function setName($name = null);

    /**
     * @return string
     */
    public function __toString();
}
