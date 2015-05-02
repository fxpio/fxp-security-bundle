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

use FOS\UserBundle\Model\GroupInterface;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acl Manipulator Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclManipulatorInterface
{
    const CLASS_TYPE = 'class';
    const OBJECT_TYPE = 'object';

    /**
     * Create an instance of PermissionContext.
     * If the security identity is not instanceof SecurityIdentityInterface, a
     * new security identity will be created using it.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $type
     * @param int                                                                         $mask
     * @param string                                                                      $field
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return PermissionContextInterface
     */
    public function createContext($sid, $domainObject, $type, $mask, $field = null, $index = 0, $granting = true, $strategy = null);

    /**
     * Get the permission mask for a given class.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     *
     * @return int
     */
    public function getClassPermission($sid, $domainObject);

    /**
     * Get the permission mask for a given domain object.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     *
     * @return int
     */
    public function getObjectPermission($sid, $domainObject);

    /**
     * Get the permission mask for a given class.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return int
     */
    public function getClassFieldPermission($sid, $domainObject, $field);

    /**
     * Get the permission mask for a given domain object field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return int
     */
    public function getObjectFieldPermission($sid, $domainObject, $field);

    /**
     * Add new permission mask for a given context. All previous permissions
     * are conserved.
     *
     * @param PermissionContextInterface $context
     *
     * @return AclManipulatorInterface
     */
    public function addPermission(PermissionContextInterface $context);

    /**
     * Add new permission mask for a given class. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function addClassPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Add new permission mask for a given object. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function addObjectPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Add new permission mask for a given class field. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function addClassFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Add new permission mask for a given object field. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function addObjectFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Replace permission mask for a given context. All previous permissions
     * are not conserved.
     *
     * @param PermissionContextInterface $context
     *
     * @return AclManipulatorInterface
     */
    public function setPermission(PermissionContextInterface $context);

    /**
     * Replace permission mask for a given class. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function setClassPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Replace permission mask for a given object. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function setObjectPermission($sid, $domainObject, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Replace permission mask for a given class field. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function setClassFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Replace permission mask for a given object field. All previous permissions
     * are conserved.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     * @param int                                                                         $index
     * @param bool                                                                        $granting
     * @param string                                                                      $strategy
     *
     * @return AclManipulatorInterface
     */
    public function setObjectFieldPermission($sid, $domainObject, $field, $mask, $index = 0, $granting = true, $strategy = null);

    /**
     * Revoke permission mask for a gient context.
     *
     * @param PermissionContextInterface $context
     *
     * @return AclManipulatorInterface
     */
    public function revokePermission(PermissionContextInterface $context);

    /**
     * Revoke permission mask for a gient class.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     *
     * @return AclManipulatorInterface
     */
    public function revokeClassPermission($sid, $domainObject, $mask);

    /**
     * Revoke permission mask for a gient object.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param int|string|array                                                            $mask
     *
     * @return AclManipulatorInterface
     */
    public function revokeObjectPermission($sid, $domainObject, $mask);

    /**
     * Revoke permission mask for a gient class field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     *
     * @return AclManipulatorInterface
     */
    public function revokeClassFieldPermission($sid, $domainObject, $field, $mask);

    /**
     * Revoke permission mask for a gient object field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     * @param int|string|array                                                            $mask
     *
     * @return AclManipulatorInterface
     */
    public function revokeObjectFieldPermission($sid, $domainObject, $field, $mask);

    /**
     * Revoke all permissions mask for a gient class.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     *
     * @return AclManipulatorInterface
     */
    public function deleteClassPermissions($sid, $domainObject);

    /**
     * Revoke all permissions mask for a gient object.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     *
     * @return AclManipulatorInterface
     */
    public function deleteObjectPermissions($sid, $domainObject);

    /**
     * Revoke all permissions mask for a gient class field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return AclManipulatorInterface
     */
    public function deleteClassFieldPermissions($sid, $domainObject, $field);

    /**
     * Revoke all permissions mask for a gient object field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return AclManipulatorInterface
     */
    public function deleteObjectFieldPermissions($sid, $domainObject, $field);

    /**
     * Delete ACL for a domain object.
     *
     * @param DomainObjectInterface|object $domainObject
     *
     * @return AclManipulatorInterface
     */
    public function deleteAclFor($domainObject);

    /**
     * @param UserInterface|GroupInterface|OrganizationInterface|TokenInterface $sid
     * @param string                                                            $oldName
     *
     * @throws InvalidArgumentException When the security identity is not a User, Group, Organization or a Token with user
     */
    public function updateUserSecurityIdentity($sid, $oldName);

    /**
     * @param UserInterface|GroupInterface|OrganizationInterface|TokenInterface $sid
     *
     * @throws InvalidArgumentException When the security identity is not a User, Group, Organization or a Token with user
     */
    public function deleteSecurityIdentity($sid);
}
