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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionEvaluator
{
    private $aclManager;

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
     * @param mixed          $domainObject
     * @param string         $mask
     *
     * @return boolean
     */
    public function hasPermission(TokenInterface $token, $domainObject, $mask)
    {
        $sis = $this->aclManager->getIdentities($token);

        return $this->aclManager->isGranted($sis, $domainObject, $mask);
    }
}
