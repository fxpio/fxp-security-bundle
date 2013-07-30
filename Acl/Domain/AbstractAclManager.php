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
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;

/**
 * Abstract class containing low-level functionality (plumbing) to be extended
 * by production AclManager (porcelain) note that none of the methods in the
 * abstract class call AclProvider#updatedAcl(); this needs to be taken care
 * of in the concrete implementation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAclManager implements AclManagerInterface
{
    protected $aclProvider;
    protected $objectIdentityRetrievalStrategy;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface              $aclProvider
     * @param ObjectIdentityRetrievalStrategyInterface $objectIdentityRetrievalStrategy
     */
    public function __construct(MutableAclProviderInterface $aclProvider, ObjectIdentityRetrievalStrategyInterface $objectIdentityRetrievalStrategy)
    {
        $this->aclProvider = $aclProvider;
        $this->objectIdentityRetrievalStrategy = $objectIdentityRetrievalStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToMask($mask)
    {
        if (is_int($mask)) {
            return $mask;
        }

        if (!is_string($mask) && !is_array($mask)) {
            throw new SecurityException('The mask must be a string, or array of string or int (the symfony mask value)');
        }

        // convert the rights to mask
        $mask = (array) $mask;
        $builder = new MaskBuilder();
        $maskConverted = null;

        try {
            foreach ($mask as $m) {
                $maskConverted = strtoupper($m);
                $builder->add($m);
            }

        } catch (\Exception $e) {
            throw new SecurityException(sprintf('The right "%s" does not exist', $maskConverted));
        }

        return $builder->get();
    }

    /**
     * {@inheritDoc}
     */
    public function convertToAclName($mask)
    {
        $mb = new MaskBuilder($mask);
        $pattern = $mb->getPattern();
        $rights = array();

        if (false !== strpos($pattern, MaskBuilder::CODE_VIEW)) {
            $rights[] = 'VIEW';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_CREATE)) {
            $rights[] = 'CREATE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_EDIT)) {
            $rights[] = 'EDIT';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_DELETE)) {
            $rights[] = 'DELETE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_UNDELETE)) {
            $rights[] = 'UNDELETE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OPERATOR)) {
            $rights[] = 'OPERATOR';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_MASTER)) {
            $rights[] = 'MASTER';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OWNER)) {
            $rights[] = 'OWNER';
        }

        return $rights;
    }

    /**
     * Get the acl provider.
     *
     * @return MutableAclProviderInterface
     */
    protected function getAclProvider()
    {
        return $this->aclProvider;
    }

    /**
     * Get the object identify retrieval rule.
     *
     * @return ObjectIdentityRetrievalStrategyInterface
     */
    protected function getObjectIdentityRetrievalStrategy()
    {
        return $this->objectIdentityRetrievalStrategy;
    }

    /**
     * Creates a new list object instanceof SecurityIdentityInterface from input
     * implementing one of UserInterface, TokenInterface or RoleInterface (or
     * its string representation).
     *
     * @param string | SecurityIdentityInterface[] | UserInterface[] | TokenInterface[] | RoleInterface[] $identities
     *
     * @return SecurityIdentityInterface[]
     *
     * @throws InvalidIdentityException
     */
    protected function doCreateSecurityIdentities($identities)
    {
        $sids = array();

        if (!is_array($identities)) {
            $identities = array($identities);
        }

        foreach ($identities as $identity) {
            if ($identity instanceof SecurityIdentityInterface) {
                continue;
            }

            if (!$identity instanceof UserInterface
                    && !$identity instanceof TokenInterface
                    && !$identity instanceof RoleInterface
                    && !is_string($identity)) {
                $str = 'Identity must implement one of: UserInterface, TokenInterface, RoleInterface';

                if (is_object($identity)) {
                    $str .= sprintf(' (%s given)', get_class($identity));
                }

                throw new \InvalidArgumentException($str);
            }

            $securityIdentity = null;

            if ($identity instanceof UserInterface) {
                $securityIdentity = UserSecurityIdentity::fromAccount($identity);

            } elseif ($identity instanceof TokenInterface) {
                $securityIdentity = UserSecurityIdentity::fromToken($identity);

            } elseif ($identity instanceof RoleInterface) {
                $securityIdentity = new RoleSecurityIdentity($identity->getRole());

            } elseif (is_string($identity)) {
                $securityIdentity = new RoleSecurityIdentity($identity);
            }

            if (!$securityIdentity instanceof SecurityIdentityInterface) {
                throw new \InvalidArgumentException('Couldn\'t create a valid SecurityIdentity with the provided identity information');
            }

            $sids[] = $securityIdentity;
        }

        return $sids;
    }

    /**
     * Returns an instance of PermissionContext. If !$securityIdentity
     * instanceof SecurityIdentityInterface, a new security identity will be
     * created using it.
     *
     * @param string  $type
     * @param mixed   $securityIdentity
     * @param integer $mask
     * @param boolean $granting
     *
     * @return PermissionContext
     */
    protected function doCreatePermissionContext($type, $securityIdentity, $mask, $index = 0, $granting = true, $grantingRule = null)
    {
        if (!$securityIdentity instanceof SecurityIdentityInterface) {
            $securityIdentities = $this->doCreateSecurityIdentities($securityIdentity);
            $securityIdentity = $securityIdentities[0];
        }

        $permissionContext = new PermissionContext();
        $permissionContext->setPermissionType($type);
        $permissionContext->setSecurityIdentity($securityIdentity);
        $permissionContext->setMask($mask);
        $permissionContext->setIndex($index);
        $permissionContext->setGranting($granting);
        $permissionContext->setGrantingRule($grantingRule);

        return $permissionContext;
    }

    /**
     * Loads an ACE collection from the ACL and updates the permissions
     * (creating if no appropriate ACE exists).
     *
     * @param MutableAclInterface        $acl
     * @param PermissionContextInterface $context
     * @param boolean                    $replace_existing
     * @param string                     $field
     */
    protected function doApplyPermission(MutableAclInterface $acl, PermissionContextInterface $context, $replace_existing = false, $field = null)
    {
        $type = $context->getPermissionType();
        $aces = $this->getAces($acl, $context->getPermissionType(), $field);

        foreach ($aces as $i => $ace) {
            if ($context->equals($ace)) {
                return;
            }

            // identity equals
            if ($context->hasDifferentPermission($ace)) {
                // override permissions
                if ($replace_existing) {
                    if (null === $field) {
                        $acl->{"update{$type}Ace"}($i, $context->getMask(), $context->getGrantingRule());

                    } else {
                        $acl->{"update{$type}FieldAce"}($i, $field, $context->getMask(), $context->getGrantingRule());
                    }

                    return;

                } else {
                    // Merge all existing permissions with new permissions
                    $newRights = $this->convertToAclName($context->getMask());

                    foreach ($this->convertToAclName($ace->getMask()) as $currentRight) {
                        if (!in_array($currentRight, $newRights)) {
                            $newRights[] = $currentRight;
                        }
                    }

                    if (null === $field) {
                        $acl->{"update{$type}Ace"}($i, $this->convertToMask($newRights), $context->getGrantingRule());

                    } else {
                        $acl->{"update{$type}FieldAce"}($i, $field, $this->convertToMask($newRights, $context->getGrantingRule()));
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
                $context->getGrantingRule()
            );

        } else {
            $acl->{"insert{$type}FieldAce"}(
                $field,
                $context->getSecurityIdentity(),
                $context->getMask(),
                $context->getIndex(),
                $context->isGranting(),
                $context->getGrantingRule()
            );
        }
    }

    /**
     * Remove the permission.
     *
     * @param MutableAclInterface        $acl
     * @param PermissionContextInterface $context
     * @param string                     $field
     */
    protected function doRevokePermission(MutableAclInterface $acl, PermissionContextInterface $context, $field = null)
    {
        $type = $context->getPermissionType();
        $aces = $this->getAces($acl, $context->getPermissionType(), $field);

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
                $currentRights = $this->convertToAclName($ace->getMask());
                $removeRights = $this->convertToAclName($context->getMask());

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
                else if (null === $field) {
                    $acl->{"update{$type}Ace"}($i, $this->convertToMask($currentRights));

                } else {
                    $acl->{"update{$type}FieldAce"}($i, $field, $this->convertToMask($currentRights));
                }
            }
            // Ace for other identity (ignored)
        }
    }

    /**
     * Remove all permission.
     *
     * @param MutableAclInterface       $acl
     * @param SecurityIdentityInterface $securityIdentity
     * @param string                    $type
     * @param string                    $field
     */
    protected function doDeletePermissions(MutableAclInterface $acl, SecurityIdentityInterface $securityIdentity, $type = 'object', $field = null)
    {
        $aces = $this->getAces($acl, $type, $field);

        foreach ($aces as $i => $ace) {
            if ($ace->getSecurityIdentity() == $securityIdentity) {
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
        if (null === $field) {
            return $acl->{"get{$type}Aces"}();
        }

        return $acl->{"get{$type}FieldAces"}($field);
    }
}
