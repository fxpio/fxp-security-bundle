<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

use Symfony\Component\Security\Acl\Model\AuditableEntryInterface;

/**
 * Define the permission context.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionContextInterface
{
    /**
     * Get the mask number.
     *
     * @return int
     */
    public function getMask();

    /**
     * get the security identity.
     *
     * @return Symfony\Component\Security\Acl\Model\SecurityIdentityInterface\SecurityIdentityInterface
     */
    public function getSecurityIdentity();

    /**
     * Get permission type.
     *
     * @return string
     */
    public function getPermissionType();

    /**
     * Get index.
     *
     * @return int
     */
    public function getIndex();

    /**
     * Check if granting.
     *
     * @return boolean
     */
    public function isGranting();

    /**
     * Get granting rule.
     *
     * @return string
     */
    public function getGrantingRule();

    /**
     * Check if the ACE is equals.
     *
     * @param AuditableEntryInterface $ace
     *
     * @return boolean
     */
    public function equals(AuditableEntryInterface $ace);

    /**
     * Check if the ACE is different.
     *
     * @param AuditableEntryInterface $ace
     *
     * @boolean
     */
    public function hasDifferentPermission(AuditableEntryInterface $ace);
}
