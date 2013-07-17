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

use FOS\UserBundle\Model\GroupInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface DoctrineGroupInterface extends GroupInterface
{
    /**
     * Get the list of entity role.
     *
     * @return RoleInterface[] The list of Role instance
     */
    public function getEntityRoles();
}
