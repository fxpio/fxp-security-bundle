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
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Define the permission context.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionContextInterface
{
    /**
     * Set the security identity.
     *
     * @param SecurityIdentityInterface $sid
     *
     * @return PermissionContextInterface
     */
    public function setSecurityIdentity(SecurityIdentityInterface $sid);

    /**
     * get the security identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface
     */
    public function getSecurityIdentity();

    /**
     * Set the object identity.
     *
     * @param ObjectIdentityInterface $oid
     *
     * @return PermissionContextInterface
     */
    public function setObjectIdentity(ObjectIdentityInterface $oid);

    /**
     * get the object identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\ObjectIdentityInterface
     */
    public function getObjectIdentity();

    /**
     * Set the field name.
     *
     * @param string $field
     *
     * @return PermissionContextInterface
     */
    public function setField($field);

    /**
     * Get field name.
     *
     * @return string|null
     */
    public function getField();

    /**
     * Set the permission type.
     *
     * @param string $type
     *
     * @return PermissionContextInterface
     *
     * @throw InvalidArgumentException When the type is not 'object' or 'class'
     */
    public function setType($type);

    /**
     * Get permission type.
     *
     * @return string 'object' or 'class' value
     */
    public function getType();

    /**
     * Set the mask.
     *
     * @param int|string|array $mask The selected permissions, or null for all
     *
     * @return PermissionContextInterface
     */
    public function setMask($mask);

    /**
     * Get the mask number.
     *
     * @return int
     */
    public function getMask();

    /**
     * Set the index.
     *
     * @param int $index
     *
     * @return PermissionContextInterface
     */
    public function setIndex($index);

    /**
     * Get index.
     *
     * @return int
     */
    public function getIndex();

    /**
     * Set the granting.
     *
     * @param boolean $granting
     *
     * @return PermissionContextInterface
     */
    public function setGranting($granting);

    /**
     * Check if granting.
     *
     * @return boolean
     */
    public function isGranting();

    /**
     * Set the granting strategy.
     *
     * @param string $strategy
     *
     * @return PermissionContextInterface
     */
    public function setStrategy($strategy);

    /**
     * Get granting strategy.
     *
     * @return string
     */
    public function getStrategy();

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
