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

use Sonatra\Bundle\SecurityBundle\AclManipulatorEvents;
use Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManipulatorInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ACL/ACE Manipulator implementation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclManipulator extends AbstractAclManipulator implements AclManipulatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClassPermission($sid, $domainObject)
    {
        return $this->getPermission($sid, static::CLASS_TYPE, $domainObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPermission($sid, $domainObject)
    {
        return $this->getPermission($sid, static::OBJECT_TYPE, $domainObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassFieldPermission($sid, $domainObject, $field)
    {
        return $this->getPermission($sid, static::CLASS_TYPE, $domainObject, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectFieldPermission($sid, $domainObject, $field)
    {
        return $this->getPermission($sid, static::OBJECT_TYPE, $domainObject, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function addPermission(PermissionContextInterface $context)
    {
        $this->dispatcher->dispatch(AclManipulatorEvents::ADD, new AclManipulatorEvent($context));
        $oid = $context->getObjectIdentity();

        try {
            $acl = $this->aclProvider->createAcl($oid);
        } catch (AclAlreadyExistsException $e) {
            $acl = $this->aclProvider->findAcl($oid);
        }

        $this->doApplyPermission($acl, $context, false);

        $this->aclProvider->updateAcl($acl);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addClassPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask, null, $index, $granting, $strategy);

        return $this->addPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask, null, $index, $granting, $strategy);

        return $this->addPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function addClassFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask, $field, $index, $granting, $strategy);

        return $this->addPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask, $field, $index, $granting, $strategy);

        return $this->addPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function setPermission(PermissionContextInterface $context)
    {
        $this->dispatcher->dispatch(AclManipulatorEvents::SET, new AclManipulatorEvent($context));
        $oid = $context->getObjectIdentity();

        try {
            $acl = $this->aclProvider->createAcl($oid);
        } catch (AclAlreadyExistsException $e) {
            $acl = $this->aclProvider->findAcl($oid);
        }

        $this->doApplyPermission($acl, $context, true);

        $this->aclProvider->updateAcl($acl);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask, null, $index, $granting, $strategy);

        return $this->setPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask, null, $index, $granting, $strategy);

        return $this->setPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function setClassFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask, $field, $index, $granting, $strategy);

        return $this->setPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask, $field, $index, $granting, $strategy);

        return $this->setPermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function revokePermission(PermissionContextInterface $context)
    {
        $this->dispatcher->dispatch(AclManipulatorEvents::REVOKE, new AclManipulatorEvent($context));
        $oid = $context->getObjectIdentity();

        /* @var MutableAclInterface $acl */
        $acl = $this->aclProvider->findAcl($oid);
        $this->doRevokePermission($acl, $context);
        $this->aclProvider->updateAcl($acl);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeClassPermission($sid, $domainObject, $mask)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask);

        return $this->revokePermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeObjectPermission($sid, $domainObject, $mask)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask);

        return $this->revokePermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeClassFieldPermission($sid, $domainObject, $field, $mask)
    {
        $context = $this->createContext($sid, $domainObject, static::CLASS_TYPE, $mask, $field);

        return $this->revokePermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeObjectFieldPermission($sid, $domainObject, $field, $mask)
    {
        $context = $this->createContext($sid, $domainObject, static::OBJECT_TYPE, $mask, $field);

        return $this->revokePermission($context);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteClassPermissions($sid, $domainObject)
    {
        return $this->deletePermissions($sid, static::CLASS_TYPE, $domainObject);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectPermissions($sid, $domainObject)
    {
        return $this->deletePermissions($sid, static::OBJECT_TYPE, $domainObject);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteClassFieldPermissions($sid, $domainObject, $field)
    {
        return $this->deletePermissions($sid, static::CLASS_TYPE, $domainObject, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectFieldPermissions($sid, $domainObject, $field)
    {
        return $this->deletePermissions($sid, static::OBJECT_TYPE, $domainObject, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAclFor($domainObject)
    {
        $oid = $this->oidRetrievalStrategy->getObjectIdentity($domainObject);

        if (null === $oid || 'object' === $oid->getIdentifier()) {
            throw new InvalidDomainObjectException('The domainObject object identity is null');
        }

        $this->aclProvider->deleteAcl($oid);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSecurityIdentity($sid, $oldName)
    {
        $sid = AclUtils::convertSecurityIdentity($sid);

        if ($sid instanceof UserSecurityIdentity) {
            $this->aclProvider->updateUserSecurityIdentity($sid, $oldName);
        } elseif ($sid instanceof RoleSecurityIdentity) {
            $this->aclProvider->updateRoleSecurityIdentity($sid, $oldName);
        } else {
            $str = 'Identity must implement one of: UserInterface, GroupInterface, OrganizationInterface, TokenInterface, UserSecurityIdentity or RoleSecurityIdentity';
            throw new InvalidArgumentException($str);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSecurityIdentity($sid)
    {
        $sid = AclUtils::convertSecurityIdentity($sid);

        $this->aclProvider->deleteSecurityIdentity($sid);
    }

    /**
     * Revoke all permissions on class or object or class field or object field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param string                                                                      $type
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return AclManipulatorInterface
     */
    protected function deletePermissions($sid, $type, $domainObject, $field = null)
    {
        $sid = AclUtils::convertSecurityIdentity($sid);
        $oid = $this->oidRetrievalStrategy->getObjectIdentity($domainObject);
        $ctx = $this->createContext($sid, $oid, $type, 0, $field);
        $this->dispatcher->dispatch(AclManipulatorEvents::DELETE, new AclManipulatorEvent($ctx));
        /* @var MutableAclInterface $acl */
        $acl = $this->aclProvider->findAcl($ctx->getObjectIdentity());
        $this->doDeletePermissions($acl, $ctx->getSecurityIdentity(), $ctx->getType(), $ctx->getField());
        $this->aclProvider->updateAcl($acl);

        return $this;
    }
}
