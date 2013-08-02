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

use Symfony\Component\Security\Acl\Model\AuditableEntryInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManipulatorInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;

/**
 * Implementation of persmission context.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionContext implements PermissionContextInterface
{
    protected $sid;
    protected $oid;
    protected $field;
    protected $type;
    protected $mask;
    protected $index;
    protected $granting;
    protected $strategy;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface $sid
     * @param ObjectIdentityInterface   $oid
     * @param string                    $type
     * @param int                       $mask
     */
    public function __construct(SecurityIdentityInterface $sid,
            ObjectIdentityInterface $oid, $type, $mask)
    {
        $this->setSecurityIdentity($sid);
        $this->setObjectIdentity($oid);
        $this->setType($type);
        $this->setMask($mask);
        $this->index = 0;
        $this->granting = true;
    }

    /**
     * {@inheritDoc}
     */
    public function setSecurityIdentity(SecurityIdentityInterface $sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentity()
    {
        return $this->sid;
    }

    /**
     * {@inheritDoc}
     */
    public function setObjectIdentity(ObjectIdentityInterface $oid)
    {
        $this->oid = $oid;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentity()
    {
        return $this->oid;
    }

    /**
     * {@inheritDoc}
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        $type = strtolower($type);

        if (!in_array($type, array(AclManipulatorInterface::OBJECT_TYPE, AclManipulatorInterface::CLASS_TYPE))) {
            throw new \InvalidArgumentException('The value of permission type must be "object" or "class"');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function setMask($mask)
    {
        $this->mask = AclUtils::convertToMask($mask);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * {@inheritDoc}
     */
    public function setIndex($index)
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy()
    {
        return $this->strategy;
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
