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
class FieldPermissionEvaluator
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
     * @param string         $field
     * @param string         $mask
     *
     * @return boolean
     */
    public function hasFieldPermission(TokenInterface $token, $domainObject, $field, $mask)
    {
        $sis = $this->aclManager->getIdentities($token);

        return $this->aclManager->isFieldGranted($sis, $domainObject, $field, $mask);
    }
}
