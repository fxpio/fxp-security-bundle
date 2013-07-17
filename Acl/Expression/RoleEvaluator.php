<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Expression;

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleEvaluator
{
    private $aclManager;
    private $cache;

    /**
     * Constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * Check if token ha role (with role hierarchy and group).
     *
     * @param TokenInterface $token
     * @param string         $role
     *
     * @return boolean
     */
    public function hasRole(TokenInterface $token, $role)
    {
        if (isset($this->cache[$role])) {
            return $this->cache[$role];
        }

        $identities = $this->aclManager->getIdentities($token);
        $this->cache[$role] = false;

        foreach ($identities as $i => $identity) {
            if ($identity instanceof RoleInterface && $role === $identity->getRole()) {
                $this->cache[$role] = true;
                break;
            }
        }

        return $this->cache[$role];
    }
}
