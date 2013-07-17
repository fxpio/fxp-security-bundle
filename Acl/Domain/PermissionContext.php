<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Model\AuditableEntryInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;

/**
 * Implementation of persmission context.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionContext implements PermissionContextInterface
{
    protected $permissionMask;
    protected $securityIdentity;
    protected $permissionType;
    protected $index = 0;
    protected $granting;
    protected $grantingRule;

    /**
     * Set the mask.
     *
     * @param integer $mask permission mask, or null for all
     *
     * @return PermissionContextInterface
     */
    public function setMask($mask)
    {
        $this->permissionMask = $mask;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMask()
    {
        return $this->permissionMask;
    }

    /**
     * Set the security identity.
     *
     * @param SecurityIdentityInterface $securityIdentity
     *
     * @return PermissionContextInterface
     */
    public function setSecurityIdentity(SecurityIdentityInterface $securityIdentity)
    {
        $this->securityIdentity = $securityIdentity;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentity()
    {
        return $this->securityIdentity;
    }

    /**
     * Set the permission type.
     *
     * @param string $type
     *
     * @return PermissionContextInterface
     */
    public function setPermissionType($type)
    {
        $this->permissionType = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionType()
    {
        return $this->permissionType;
    }

    /**
     * Set the index.
     *
     * @param int $index
     *
     * @return PermissionContextInterface
     */
    public function setIndex($index = 0)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set the granting.
     *
     * @param boolean $granting
     *
     * @return PermissionContextInterface
     */
    public function setGranting($granting)
    {
        $this->granting = $granting;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranting()
    {
        return $this->granting;
    }

    /**
     * Set the granting rule.
     *
     * @param string $rule
     *
     * @return PermissionContextInterface
     */
    public function setGrantingRule($rule = null)
    {
        $this->grantingRule = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGrantingRule()
    {
        return $this->grantingRule;
    }

    /**
     * {@inheritDoc}
     */
    public function equals(AuditableEntryInterface $ace)
    {
        return $ace->getSecurityIdentity() == $this->getSecurityIdentity()
                && $ace->isGranting() === $this->isGranting()
                && $ace->getMask() === $this->getMask();
    }

    /**
     * {@inheritDoc}
     */
    public function hasDifferentPermission(AuditableEntryInterface $ace)
    {
        return $ace->getSecurityIdentity() == $this->getSecurityIdentity()
                && $ace->isGranting() === $this->isGranting()
                && $ace->getMask() !== $this->getMask();
    }
}
