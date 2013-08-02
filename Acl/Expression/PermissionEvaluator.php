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
     * Check if token has permission of domain object.
     *
     * @param TokenInterface                      $token
     * @param DomainObjectInterface|object|string $domainObject
     * @param int|string|array                    $mask
     *
     * @return boolean
     */
    public function hasPermission(TokenInterface $token, $domainObject, $mask)
    {
        $sids = $this->aclManager->getSecurityIdentities($token);

        return $this->aclManager->isGranted($sids, $domainObject, $mask);
    }
}
