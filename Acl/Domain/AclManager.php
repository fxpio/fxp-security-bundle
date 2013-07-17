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

use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Domain\AbstractAclManager;
use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;
use Sonatra\Bundle\SecurityBundle\Core\Role\GroupRoleInterface;

/**
 * ACL Manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclManager extends AbstractAclManager
{
    /**
     * @var AclRuleManagerInterface
     */
    protected $aclRuleManager;

    /**
     * @var GroupRoleInterface
     */
    protected $groupRole;

    /**
     * @var RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @var array
     */
    protected $tokenIdentityCache = array();

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface              $aclProvider
     * @param ObjectIdentityRetrievalStrategyInterface $objectIdentityRetrievalStrategy
     * @param AclRuleInterface                         $aclRule
     */
    public function __construct(MutableAclProviderInterface $aclProvider,
            ObjectIdentityRetrievalStrategyInterface $objectIdentityRetrievalStrategy,
            AclRuleManagerInterface $aclRuleManager,
            GroupRoleInterface $groupRole = null,
            RoleHierarchyInterface $roleHierarchy = null)
    {
        parent::__construct($aclProvider, $objectIdentityRetrievalStrategy);

        $this->aclRuleManager = $aclRuleManager;
        $this->groupRole = $groupRole;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassPermission($securityIdentity, $domainObject)
    {
        return $this->getPermission($securityIdentity, 'class', $domainObject);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectPermission($securityIdentity, $domainObject)
    {
        return $this->getPermission($securityIdentity, 'object', $domainObject);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassFieldPermission($securityIdentity, $domainObject, $field)
    {
        return $this->getPermission($securityIdentity, 'class', $domainObject, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectFieldPermission($securityIdentity, $domainObject, $field)
    {
        return $this->getPermission($securityIdentity, 'object', $domainObject, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function addClassPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->addPermission($securityIdentity, 'class', $domainObject, $mask, false, null, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addObjectPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->addPermission($securityIdentity, 'object', $domainObject, $mask, false, null, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setClassPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->setPermission($securityIdentity, 'class', $domainObject, $mask, null, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setObjectPermission($securityIdentity, $domainObject, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->setPermission($securityIdentity, 'object', $domainObject, $mask, null, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addClassFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->addPermission($securityIdentity, 'class', $domainObject, $mask, false, $field, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addObjectFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->addPermission($securityIdentity, 'object', $domainObject, $mask, false, $field, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setClassFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->setPermission($securityIdentity, 'class', $domainObject, $mask, $field, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setObjectFieldPermission($securityIdentity, $domainObject, $field, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->setPermission($securityIdentity, 'object', $domainObject, $mask, $field, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeClassPermission($securityIdentity, $domainObject, $mask)
    {
        $this->revokePermission($securityIdentity, 'class', $domainObject, $mask);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeObjectPermission($securityIdentity, $domainObject, $mask)
    {
        $this->revokePermission($securityIdentity, 'object', $domainObject, $mask);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteClassPermissions($securityIdentity, $domainObject)
    {
        $this->deletePermissions($securityIdentity, 'class', $domainObject);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteObjectPermissions($securityIdentity, $domainObject)
    {
        $this->deletePermissions($securityIdentity, 'object', $domainObject);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeClassFieldPermission($securityIdentity, $domainObject, $field, $mask)
    {
        $this->revokePermission($securityIdentity, 'class', $domainObject, $mask, $field);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeObjectFieldPermission($securityIdentity, $domainObject, $field, $mask)
    {
        $this->revokePermission($securityIdentity, 'object', $domainObject, $mask, $field);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteClassFieldPermissions($securityIdentity, $domainObject, $field)
    {
        $this->deletePermissions($securityIdentity, 'class', $domainObject, $field);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteObjectFieldPermissions($securityIdentity, $domainObject, $field)
    {
        $this->deletePermissions($securityIdentity, 'object', $domainObject, $field);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted($securityIdentities, $domainObject, $mask)
    {
        $granted = false;
        $field = null;
        $masks = array();

        // generate mask
        if (!is_array($mask)) {
            $mask = array($mask);
        }

        foreach ($mask as $m) {
            $masks[] = $this->convertToMask($m);
        }

        // get the object or class
        if ($domainObject instanceof FieldVote) {
            $field = $domainObject->getField();
            $domainObject = $domainObject->getDomainObject();
        }

        $securityIdentities = $this->doCreateSecurityIdentities($securityIdentities);
        $rule = $this->getRule($mask, $domainObject, $field);
        $definition = $this->aclRuleManager->getDefinition($rule);
        $arc = new AclRuleContext($this, $this->aclRuleManager, $securityIdentities);

        return $definition->isGranted($arc, $domainObject, $masks, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function isFieldGranted($securityIdentities, $domainObject, $field, $mask)
    {
        // override the field in FieldVote with the new field name
        if ($domainObject instanceof FieldVote) {
            $domainObject = $domainObject->getDomainObject();
        }

        return $this->isGranted($securityIdentities,
                new FieldVote($domainObject, $field), $mask);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAclFor($domainObject)
    {
        $oid = $this->getObjectIdentifier($domainObject);

        if (null === $oid || 'object' === $oid->getIdentifier()) {
            throw new InvalidDomainObjectException("The domain object identity is null");
        }

        $this->getAclProvider()->deleteAcl($oid);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function preloadAcls(array $objects)
    {
        $oids = array();

        foreach ($objects as $object) {
            $oid = $this->getObjectIdentityRetrievalStrategy()->getObjectIdentity($object);

            if (null !== $oid) {
                $oids[] = $oid;
            }
        }

        $acls = $this->getAclProvider()->findAcls($oids);

        return $acls;
    }

    /**
     * {@inheritDoc}
     */
    public function getRule($type, $domainObject, $field = null)
    {
        if (is_array($type)) {
            $type = $type[0];
        }

        if (is_int($type)) {
            $type = $this->convertToAclName($type);
        }

        $classname = $this->getDomainObjectClassname($domainObject);

        if ($domainObject instanceof FieldVote) {
            $field = $domainObject->getField();
        }

        return $this->aclRuleManager->getRule($type, $classname, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities(TokenInterface $token = null)
    {
        if (null === $token) {
            return array();
        }

        if (isset($this->tokenIdentityCache[$token->getUsername()])) {
            return $this->tokenIdentityCache[$token->getUsername()];
        }

        $identities = array($token);
        $roles = $token->getRoles();

        if (null !== $this->groupRole) {
            $roles = $this->groupRole->getReachableRoles($token);
        }

        if (null === $this->roleHierarchy) {
            return array_merge($identities, $roles);
        }

        $identities = array_merge($identities, $this->roleHierarchy->getReachableRoles($roles));
        $this->tokenIdentityCache[$token->getUsername()] = $identities;

        return $identities;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentities($identities)
    {
        return $this->doCreateSecurityIdentities($identities);
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentifier($domainObject)
    {
        if ($domainObject instanceof FieldVote) {
            $domainObject = $domainObject->getDomainObject();
        }

        if ($domainObject instanceof ObjectIdentity) {
            return $domainObject;
        }

        if (is_string($domainObject)) {
            return new ObjectIdentity('class', $this->getDomainObjectClassname($domainObject));
        }

        // valid object identity with domain instance
        $oid = $this->getObjectIdentityRetrievalStrategy()->getObjectIdentity($domainObject);

        // object identity is not a valid object
        if (null === $oid) {
            return new ObjectIdentity('object', $this->getDomainObjectClassname($domainObject));
        }

        return $oid;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomainObjectClassname($domainObject)
    {
        if ($domainObject instanceof FieldVote) {
            $domainObject = $domainObject->getDomainObject();
        }

        if ($domainObject instanceof ObjectIdentity) {
            $domainObject = $domainObject->getType();
        }

        if (is_object($domainObject)) {
            $domainObject = get_class($domainObject);
        }

        if (!is_string($domainObject)) {
            throw new SecurityException("The domain object must be an string for 'class' type");
        }

        return $domainObject;
    }

    /**
     * {@inheritdoc}
     */
    public function doIsGranted(array $securityIdentities, array $masks, ObjectIdentityInterface $oid, $field = null)
    {
        try {
            $acl = $this->getAclProvider()->findAcl($oid);
            $masks = $this->getAllMasks($masks);

            if (null === $field) {
                return $acl->isGranted($masks, $securityIdentities);
            }

            return $acl->isFieldGranted($field, $masks, $securityIdentities);

        } catch (AclNotFoundException $e) {
        } catch (NoAceFoundException $e) {
        }

        return false;
    }

    /**
     * Get the permission on class or object or class field, or object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param string                                         $type
     * @param mixed                                          $domainObject
     * @param string                                         $field
     *
     * @return int
     */
    protected function getPermission($securityIdentity, $type, $domainObject, $field = null)
    {
        $oid = $this->getObjectIdentifier($domainObject);
        $securityIdentity = $this->getSecurityIdentity($securityIdentity);

        $rights = array();

        try {
            $acl = $this->getAclProvider()->findAcl($oid);

        } catch (AclNotFoundException $e) {
            return $this->convertToMask($rights);

        } catch (NoAceFoundException $e) {
            return $this->convertToMask($rights);
        }

        $aces = $this->getAces($acl, $type, $field);

        foreach ($aces as $i => $ace) {
            if ($ace->getSecurityIdentity() == $securityIdentity) {
                $rights = array_merge($rights, $this->convertToAclName($ace->getMask()));
            }
        }

        // remove doublon
        $rights = array_unique($rights);

        return $this->convertToMask($rights);
    }

    /**
     * Add permission on class or object or class field or object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param string                                         $type
     * @param mixed                                          $domainObject
     * @param int | string | array                           $mask
     * @param boolean                                        $replace_existing
     * @param string                                         $field
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    protected function addPermission($securityIdentity, $type, $domainObject, $mask, $replace_existing = false, $field = null, $index = 0, $granting = true, $grantingRule = null)
    {
        $oid = $this->getObjectIdentifier($domainObject);
        $securityIdentity = $this->getSecurityIdentity($securityIdentity);
        $mask = $this->convertToMask($mask);
        $context = $this->doCreatePermissionContext($type, $securityIdentity, $mask, $index, $granting, $grantingRule);

        try {
            $acl = $this->getAclProvider()->createAcl($oid);

        } catch (AclAlreadyExistsException $e) {
            $acl = $this->getAclProvider()->findAcl($oid);
        }

        $this->doApplyPermission($acl, $context, $replace_existing, $field);

        $this->getAclProvider()->updateAcl($acl);

        return $this;
    }

    /**
     * Replace permission on class or object or class field or object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param string                                         $type
     * @param mixed                                          $domainObject
     * @param int | string | array                           $mask
     * @param string                                         $field
     * @param int                                            $index
     * @param boolean                                        $granting
     * @param string                                         $grantingRule
     *
     * @return AclManagerInterface
     */
    protected function setPermission($securityIdentity, $type, $domainObject, $mask, $field = null, $index = 0, $granting = true, $grantingRule = null)
    {
        $this->addPermission($securityIdentity, $type, $domainObject, $mask, true, $field, $index, $granting, $grantingRule);

        return $this;
    }

    /**
     * Revoke permission on class or object or class field or object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param string                                         $type
     * @param object | string                                $domainObject
     * @param int | string | array                           $mask
     * @param string                                         $field
     *
     * @return AclManagerInterface
     */
    protected function revokePermission($securityIdentity, $type, $domainObject, $mask, $field = null)
    {
        $oid = $this->getObjectIdentifier($domainObject);
        $securityIdentity = $this->getSecurityIdentity($securityIdentity);
        $mask = $this->convertToMask($mask);
        $context = $this->doCreatePermissionContext($type, $securityIdentity, $mask);

        $acl = $this->getAclProvider()->findAcl($oid);
        $this->doRevokePermission($acl, $context, $field);
        $this->getAclProvider()->updateAcl($acl);

        return $this;
    }

    /**
     * Revoke all permissions on class or object or class field or object field.
     *
     * @param UserInterface | TokenInterface | RoleInterface $securityIdentity
     * @param string                                         $type
     * @param mixed                                          $domainObject
     * @param string                                         $field
     *
     * @return AclManagerInterface
     */
    protected function deletePermissions($securityIdentity, $type, $domainObject, $field = null)
    {
        $oid = $this->getObjectIdentifier($domainObject);
        $securityIdentity = $this->getSecurityIdentity($securityIdentity);
        $acl = $this->getAclProvider()->findAcl($oid);
        $this->doDeletePermissions($acl, $securityIdentity, $type, $field);
        $this->getAclProvider()->updateAcl($acl);

        return $this;
    }

    /**
     * Get security identity.
     *
     * @param string $securityIdentity
     *
     * @return SecurityIdentityInterface
     */
    protected function getSecurityIdentity($securityIdentity)
    {
        if (null === $securityIdentity) {
            throw new SecurityException("The Security Identity must be present");
        }

        $securityIdentities = $this->doCreateSecurityIdentities($securityIdentity);

        if (0 === count($securityIdentities)) {
            throw new SecurityException("The Security Identity not found");
        }

        return $securityIdentities[0];
    }

    /**
     * Get the all masks for allow the access on greater permissions define by
     * the Symfony2 ACL Advanced Pre-Authorization Decisions Documentation.
     *
     * @param array $masks The masks
     *
     * @return array The all masks to find the access
     */
    protected function getAllMasks(array $masks)
    {
        $allMasks = array();

        foreach ($masks as $mask) {

            switch ($mask) {
                case MaskBuilder::MASK_VIEW:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_VIEW,
                        MaskBuilder::MASK_EDIT,
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_EDIT:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_EDIT,
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_CREATE:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_CREATE,
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_DELETE:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_DELETE,
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_UNDELETE:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_UNDELETE,
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_OPERATOR:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_OPERATOR,
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_MASTER:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_MASTER,
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_OWNER:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_OWNER,
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;

                case MaskBuilder::MASK_IDDQD:
                    $allMasks = array_merge($allMasks, array(
                        MaskBuilder::MASK_IDDQD,
                    ));
                    break;
            }
        }

        return array_unique($allMasks);
    }
}
