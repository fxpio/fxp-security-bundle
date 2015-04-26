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

use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AuditableEntryInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManipulatorInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Abstract class containing low-level functionality to be extended by
 * production AclManipulator.
 * Note that none of the methods in the abstract class call
 * AclProvider#updatedAcl(); this needs to be taken care of in the concrete
 * implementation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAclManipulator implements AclManipulatorInterface
{
    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $sidRetrievalStrategy;

    /**
     * @var ObjectIdentityRetrievalStrategyInterface
     */
    protected $oidRetrievalStrategy;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface                $aclProvider
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     * @param ObjectIdentityRetrievalStrategyInterface   $oidRetrievalStrategy
     */
    public function __construct(MutableAclProviderInterface $aclProvider, SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy, ObjectIdentityRetrievalStrategyInterface $oidRetrievalStrategy)
    {
        $this->aclProvider = $aclProvider;
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->oidRetrievalStrategy = $oidRetrievalStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function createContext($sid, $domainObject, $type, $mask, $field = null, $index = 0, $granting = true, $strategy = null)
    {
        $sid = AclUtils::convertSecurityIdentity($sid);
        $oid = $this->oidRetrievalStrategy->getObjectIdentity($domainObject);

        $context = new PermissionContext($sid, $oid, $type, $mask);
        $context->setField($field);
        $context->setIndex($index);
        $context->setGranting($granting);
        $context->setStrategy($strategy);

        return $context;
    }

    /**
     * Get the permission on class or object or class field, or object field.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $sid
     * @param string                                                                      $type
     * @param DomainObjectInterface|object|string                                         $domainObject
     * @param string                                                                      $field
     *
     * @return int
     */
    protected function getPermission($sid, $type, $domainObject, $field = null)
    {
        $sid = AclUtils::convertSecurityIdentity($sid);
        $oid = $this->oidRetrievalStrategy->getObjectIdentity($domainObject);
        $rights = array();

        try {
            /* @var MutableAclInterface $acl */
            $acl = $this->aclProvider->findAcl($oid);

        } catch (AclNotFoundException $e) {
            return AclUtils::convertToMask($rights);

        } catch (NoAceFoundException $e) {
            return AclUtils::convertToMask($rights);
        }

        $aces = $this->getAces($acl, $type, $field);

        /* @var EntryInterface $ace */
        foreach ($aces as $ace) {
            if ($ace->getSecurityIdentity() == $sid) {
                $rights = array_merge($rights, AclUtils::convertToAclName($ace->getMask()));
            }
        }

        // remove doublon
        $rights = array_unique($rights);

        return AclUtils::convertToMask($rights);
    }

    /**
     * Loads an ACE collection from the ACL and updates the permissions
     * (creating if no appropriate ACE exists).
     *
     * @param MutableAclInterface        $acl
     * @param PermissionContextInterface $context
     * @param bool                       $override
     */
    protected function doApplyPermission(MutableAclInterface $acl, PermissionContextInterface $context, $override = false)
    {
        $field = $context->getField();
        $type = $this->validateType($context->getType());
        $aces = $this->getAces($acl, $type, $field);

        /* @var AuditableEntryInterface $ace */
        foreach ($aces as $i => $ace) {
            if ($context->equals($ace)) {
                return;
            }

            // identity equals
            if ($context->hasDifferentPermission($ace)) {
                // override permissions
                if ($override) {
                    if (null === $field) {
                        $acl->{"update{$type}Ace"}($i, $context->getMask(), $context->getStrategy());

                    } else {
                        $acl->{"update{$type}FieldAce"}($i, $field, $context->getMask(), $context->getStrategy());
                    }

                    return;

                } else {
                    // Merge all existing permissions with new permissions
                    $newRights = AclUtils::convertToAclName($context->getMask());

                    foreach (AclUtils::convertToAclName($ace->getMask()) as $currentRight) {
                        if (!in_array($currentRight, $newRights)) {
                            $newRights[] = $currentRight;
                        }
                    }

                    if (null === $field) {
                        $acl->{"update{$type}Ace"}($i, AclUtils::convertToMask($newRights), $context->getStrategy());

                    } else {
                        $acl->{"update{$type}FieldAce"}($i, $field, AclUtils::convertToMask($newRights, $context->getStrategy()));
                    }
                }

                return;
            }
        }

        // other identity
        if (null === $field) {
            $acl->{"insert{$type}Ace"}(
                $context->getSecurityIdentity(),
                $context->getMask(),
                $context->getIndex(),
                $context->isGranting(),
                $context->getStrategy()
            );

        } else {
            $acl->{"insert{$type}FieldAce"}(
                $field,
                $context->getSecurityIdentity(),
                $context->getMask(),
                $context->getIndex(),
                $context->isGranting(),
                $context->getStrategy()
            );
        }
    }

    /**
     * Remove the permission.
     *
     * @param MutableAclInterface        $acl
     * @param PermissionContextInterface $context
     */
    protected function doRevokePermission(MutableAclInterface $acl, PermissionContextInterface $context)
    {
        $field = $context->getField();
        $type = $this->validateType($context->getType());
        $aces = $this->getAces($acl, $context->getType(), $field);

        /* @var AuditableEntryInterface $ace */
        foreach ($aces as $i => $ace) {
            if ($context->equals($ace)) {
                // find ace for identity with equals permissions
                if (null === $field) {
                    $acl->{"delete{$type}Ace"}($i);

                } else {
                    $acl->{"delete{$type}FieldAce"}($i, $field);
                }

            } elseif ($context->hasDifferentPermission($ace)) {
                // find ace for identity but the permissions is different
                // Remove permissions in current permissions
                $currentRights = AclUtils::convertToAclName($ace->getMask());
                $removeRights = AclUtils::convertToAclName($context->getMask());

                foreach ($currentRights as $j => $currentRight) {
                    if (in_array($currentRight, $removeRights)) {
                        unset($currentRights[$j]);
                    }
                }

                // delete permissions
                if (empty($currentRights) && null === $field) {
                    $acl->{"delete{$type}Ace"}($i);

                } elseif (empty($currentRights)) {
                    $acl->{"delete{$type}FieldAce"}($i, $field);
                }

                // update permissions
                elseif (null === $field) {
                    $acl->{"update{$type}Ace"}($i, AclUtils::convertToMask($currentRights));

                } else {
                    $acl->{"update{$type}FieldAce"}($i, $field, AclUtils::convertToMask($currentRights));
                }
            }
            // Ace for other identity (ignored)
        }
    }

    /**
     * Remove all permission.
     *
     * @param MutableAclInterface       $acl
     * @param SecurityIdentityInterface $sid
     * @param string                    $type
     * @param string                    $field
     */
    protected function doDeletePermissions(MutableAclInterface $acl, SecurityIdentityInterface $sid, $type = 'object', $field = null)
    {
        $type = $this->validateType($type);
        $aces = $this->getAces($acl, $type, $field);

        /* @var EntryInterface $ace */
        foreach ($aces as $i => $ace) {
            if ($ace->getSecurityIdentity() == $sid) {
                if (null === $field) {
                    $acl->{"delete{$type}Ace"}($i);

                } else {
                    $acl->{"delete{$type}FieldAce"}($i, $field);
                }
            }
        }
    }

    /**
     * Get the ace collection.
     *
     * @param MutableAclInterface $acl
     * @param string              $type
     * @param string              $field
     *
     * @return array
     */
    protected function getAces(MutableAclInterface $acl, $type = 'object', $field = null)
    {
        $type = $this->validateType($type);

        if (null === $field) {
            return $acl->{"get{$type}Aces"}();
        }

        return $acl->{"get{$type}FieldAces"}($field);
    }

    /**
     * Validate and format acl type.
     *
     * @param string $type
     *
     * @throws InvalidArgumentException When the type is not 'object' or 'class'
     *
     * @return string The type formated for used in ACL/ACE methods
     */
    protected function validateType($type)
    {
        $type = strtolower($type);

        if (!in_array($type, array($this::OBJECT_TYPE, $this::CLASS_TYPE))) {
            throw new InvalidArgumentException('The permission type must be "object" or "class"');
        }

        return ucfirst($type);
    }
}
