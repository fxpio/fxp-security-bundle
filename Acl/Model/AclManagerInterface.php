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

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Acl Manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclManagerInterface
{
    // alias of mask builder
    const VIEW     = MaskBuilder::MASK_VIEW;
    const CREATE   = MaskBuilder::MASK_CREATE;
    const EDIT     = MaskBuilder::MASK_EDIT;
    const DELETE   = MaskBuilder::MASK_DELETE;
    const UNDELETE = MaskBuilder::MASK_UNDELETE;
    const OPERATOR = MaskBuilder::MASK_OPERATOR;
    const MASTER   = MaskBuilder::MASK_MASTER;
    const OWNER    = MaskBuilder::MASK_OWNER;
    const IDDQD    = MaskBuilder::MASK_IDDQD;

    /**
     * Get the permission mask for a given class.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     *
     * @return int
     */
    public function getClassPermission($securityIdentity, $domainObject);

    /**
     * Get the permission mask for a given domain object.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     *
     * @return int
     */
    public function getObjectPermission($securityIdentity, $domainObject);

    /**
     * Get the permission mask for a given class.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     *
     * @return int
     */
    public function getClassFieldPermission($securityIdentity, $domainObject, $field);

    /**
     * Get the permission mask for a given domain object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     *
     * @return int
     */
    public function getObjectFieldPermission($securityIdentity, $domainObject, $field);

    /**
     * Add new permission mask for a given class. All previous permissions
     * are conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function addClassPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Add new permission mask for a given object. All previous permissions
     * are conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function addObjectPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Replace permission mask for a given class. All previous permissions
     * are not conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function setClassPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Replace permission mask for a given object. All previous permissions
     * are not conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function setObjectPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Add new permission mask for a given class field. All previous permissions
     * are conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function addClassFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Add new permission mask for a given object field. All previous permissions
     * are conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function addObjectFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Replace permission mask for a given class field. All previous permissions
     * are not conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function setClassFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Replace permission mask for a given object field. All previous permissions
     * are not conserved.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    public function setObjectFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null);

    /**
     * Revoke permission mask for a gient class.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     *
     * @return AclManagerInterface
     */
    public function revokeClassPermission($securityIdentity, $domainObject, $mask);

    /**
     * Revoke permission mask for a gient object.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param int | string | array                           $mask
     *
     * @return AclManagerInterface
     */
    public function revokeObjectPermission($securityIdentity, $domainObject, $mask);

    /**
     * Revoke all permissions mask for a gient class.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     *
     * @return AclManagerInterface
     */
    public function deleteClassPermissions($securityIdentity, $domainObject);

    /**
     * Revoke all permissions mask for a gient object.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     *
     * @return AclManagerInterface
     */
    public function deleteObjectPermissions($securityIdentity, $domainObject);

    /**
     * Revoke permission mask for a gient class field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     *
     * @return AclManagerInterface
     */
    public function revokeClassFieldPermission($securityIdentity, $domainObject, $field, $mask);

    /**
     * Revoke permission mask for a gient object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     * @param int | string | array                           $mask
     *
     * @return AclManagerInterface
     */
    public function revokeObjectFieldPermission($securityIdentity, $domainObject, $field, $mask);

    /**
     * Revoke all permissions mask for a gient class field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     *
     * @return AclManagerInterface
     */
    public function deleteClassFieldPermissions($securityIdentity, $domainObject, $field);

    /**
     * Revoke all permissions mask for a gient object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param DomainObjectInterface | object | string        $domainObject
     * @param string                                         $field
     *
     * @return AclManagerInterface
     */
    public function deleteObjectFieldPermissions($securityIdentity, $domainObject, $field);

    /**
     * Determines whether access is granted.
     *
     * @param UserInterface[] | TokenInterface[] | RoleInterface[] $securityIdentities
     * @param DomainObjectInterface | object | string              $domainObject
     * @param int | string | array                                 $mask
     *
     * @return Boolean
     */
    public function isGranted($securityIdentities, $domainObject, $mask);

    /**
     * Determines whether field access is granted. Alias of isGranted with
     * FieldVote class for $domainObject.
     *
     * @param UserInterface[] | TokenInterface[] | RoleInterface[] $securityIdentities
     * @param DomainObjectInterface | object | string              $domainObject
     * @param string                                               $field
     * @param int | string | array                                 $mask
     *
     * @return Boolean
     */
    public function isFieldGranted($securityIdentities, $domainObject, $field, $mask);

    /**
     * Delete ACL for a domain object.
     *
     * @param DomainObjectInterface | object $domainObject
     *
     * @return AclManagerInterface
     */
    public function deleteAclFor($domainObject);

    /**
     * Preload ACLs for object.
     *
     * @param object[] $objects
     *
     * @return SplObjectStorage
     */
    public function preloadAcls(array $objects);

    /**
     * Get the internal acl rule.
     *
     * @param string | int                            $type         The mask type
     * @param DomainObjectInterface | object | string $domainObject The domain
     * @param string                                  $field        The field name
     *
     * @return string
     */
    public function getRule($type, $domainObject, $field = null);

    /**
     * Get itendities for security.
     *
     * @param TokenInterface $token
     *
     * @return array The list of UserInterface, TokenInterface and RoleInterface
     */
    public function getIdentities(TokenInterface $token = null);

    /**
     * Creates a new list object instanceof SecurityIdentityInterface from input
     * implementing one of UserInterface, TokenInterface or RoleInterface (or
     * its string representation).
     *
     * @param string | SecurityIdentityInterface[] | UserInterface[] | TokenInterface[] | RoleInterface[] $identities
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities($identities);

    /**
     * Get the object identifier for manipulator permission provider.
     *
     * @param FieldVote | ObjectIdentity | object | string $domainObject
     *
     * @return ObjectIdentityInterface
     */
    public function getObjectIdentifier($domainObject);

    /**
     * Get the class name of domain object.
     *
     * @param FieldVote | ObjectIdentity | object | string $domainObject
     *
     * @throws SecurityException When the domain object is not a string for class type
     *
     * @return string
     */
    public function getDomainObjectClassname($domainObject);

    /**
     * Check if the security identities are granted on object identity.
     * Used in isGranted() by ACL Rule Definition.
     *
     * @param SecurityIdentityInterface[] $securityIdentities
     * @param array                       $masks
     * @param ObjectIdentityInterface     $oid
     * @param string                      $field
     */
    public function doIsGranted(array $securityIdentities, array $masks, ObjectIdentityInterface $oid, $field = null);
}
