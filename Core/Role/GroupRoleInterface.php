<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * GroupRole get the all roles in all group of token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface GroupRoleInterface
{
    /**
     * Get the all roles on user with roles associated in groups.
     *
     * @param TokenInterface $token
     *
     * @return RoleInterface[]
     */
    public function getReachableRoles(TokenInterface $token);
}
