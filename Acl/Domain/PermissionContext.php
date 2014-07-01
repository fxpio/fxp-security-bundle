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
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;

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
     * {@inheritdoc}
     */
    public function setSecurityIdentity(SecurityIdentityInterface $sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentity()
    {
        return $this->sid;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectIdentity(ObjectIdentityInterface $oid)
    {
        $this->oid = $oid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity()
    {
        return $this->oid;
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $type = strtolower($type);

        if (!in_array($type, array(AclManipulatorInterface::OBJECT_TYPE, AclManipulatorInterface::CLASS_TYPE))) {
            throw new InvalidArgumentException('The value of permission type must be "object" or "class"');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setMask($mask)
    {
        $this->mask = AclUtils::convertToMask($mask);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * {@inheritdoc}
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function setGranting($granting)
    {
        $this->granting = $granting;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranting()
    {
        return $this->granting;
    }

    /**
     * {@inheritdoc}
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(AuditableEntryInterface $ace)
    {
        return $ace->getSecurityIdentity() == $this->getSecurityIdentity()
                && $ace->isGranting() === $this->isGranting()
                && $ace->getMask() === $this->getMask();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDifferentPermission(AuditableEntryInterface $ace)
    {
        return $ace->getSecurityIdentity() == $this->getSecurityIdentity()
                && $ace->isGranting() === $this->isGranting()
                && $ace->getMask() !== $this->getMask();
    }
}
