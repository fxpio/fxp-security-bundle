<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Util;

use Sonatra\Bundle\SecurityBundle\Acl\Domain\GroupSecurityIdentity;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use FOS\UserBundle\Model\GroupInterface;

/**
 * Class related functionality for acl manipulation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclUtils
{
    /**
     * Convert the acl name or the array of acl name to mask.
     *
     * @param int|string|array $mask
     *
     * @return integer
     *
     * @throws InvalidArgumentException When the mask is not a string, array of string or int (the symfony mask value)
     * @throws InvalidArgumentException When the right does not exist
     */
    public static function convertToMask($mask)
    {
        if (is_int($mask)) {
            return $mask;
        }

        if (!is_string($mask) && !is_array($mask)) {
            throw new InvalidArgumentException('The mask must be a string, or array of string or int (the symfony mask value)');
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
            throw new InvalidArgumentException(sprintf('The right "%s" does not exist', $maskConverted));
        }

        return $builder->get();
    }

    /**
     * Convert the mask to array of acl name.
     *
     * @param int $mask The mask
     *
     * @return array The list of permission (in string)
     *
     * @throws InvalidArgumentException When the mask parameter is not a int
     */
    public static function convertToAclName($mask)
    {
        if (!is_int($mask)) {
            throw new InvalidArgumentException("The mask must be a int");
        }

        $mb = new MaskBuilder($mask);
        $pattern = $mb->getPattern();
        $rights = array();

        if (false !== strpos($pattern, MaskBuilder::CODE_VIEW)) {
            $rights[] = BasicPermissionMap::PERMISSION_VIEW;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_CREATE)) {
            $rights[] = BasicPermissionMap::PERMISSION_CREATE;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_EDIT)) {
            $rights[] = BasicPermissionMap::PERMISSION_EDIT;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_DELETE)) {
            $rights[] = BasicPermissionMap::PERMISSION_DELETE;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_UNDELETE)) {
            $rights[] = BasicPermissionMap::PERMISSION_UNDELETE;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OPERATOR)) {
            $rights[] = BasicPermissionMap::PERMISSION_OPERATOR;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_MASTER)) {
            $rights[] = BasicPermissionMap::PERMISSION_MASTER;
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OWNER)) {
            $rights[] = BasicPermissionMap::PERMISSION_OWNER;
        }

        return $rights;
    }

    /**
     * Creates a new object of SecurityIdentityInterface from input implementing
     * one of UserInterface, RoleInterface or string representation.
     *
     * @param RoleInterface|UserInterface|TokenInterface|string|SecurityIdentityInterface $identity
     *
     * @return SecurityIdentityInterface
     *
     * @throws InvalidArgumentException
     */
    public static function convertSecurityIdentity($identity)
    {
        $sids = static::convertSecurityIdentities($identity);

        return array_shift($sids);
    }

    /**
     * Creates a new list of SecurityIdentityInterface from input implementing
     * one of UserInterface, RoleInterface or string representation.
     *
     * @param RoleInterface|RoleInterface[]|UserInterface|UserInterface[]|TokenInterface|TokenInterface[]|string|string[]|SecurityIdentityInterface|SecurityIdentityInterface[] $identities
     *
     * @return SecurityIdentityInterface[]
     *
     * @throws InvalidArgumentException
     */
    public static function convertSecurityIdentities($identities)
    {
        $sids = array();

        if (!is_array($identities)) {
            $identities = array($identities);
        }

        foreach ($identities as $identity) {
            if ($identity instanceof SecurityIdentityInterface) {
                $sids[] = $identity;

            } elseif ($identity instanceof UserInterface) {
                $sids[] = UserSecurityIdentity::fromAccount($identity);

            } elseif ($identity instanceof GroupInterface) {
                $sids[] = GroupSecurityIdentity::fromAccount($identity);

            } elseif ($identity instanceof TokenInterface) {
                $sids[] = UserSecurityIdentity::fromToken($identity);

            } elseif ($identity instanceof RoleInterface) {
                $sids[] = new RoleSecurityIdentity($identity->getRole());

            } elseif (is_string($identity)) {
                $sids[] = new RoleSecurityIdentity($identity);

            } else {
                $str = 'Identity must implement one of: RoleInterface, UserInterface, GroupInterface or string';

                if (is_object($identity)) {
                    $str .= sprintf(' (%s given)', get_class($identity));
                }

                throw new InvalidArgumentException($str);
            }
        }

        return $sids;
    }

    /**
     * Convert DomainObject class to the string of object classname.
     *
     * @param FieldVote|ObjectIdentity|object|string $domainObject
     *
     * @throws InvalidArgumentException When the domain object is not a string for class type
     *
     * @return string
     */
    public static function convertDomainObjectToClassname($domainObject)
    {
        if ($domainObject instanceof FieldVote) {
            $domainObject = $domainObject->getDomainObject();
        }

        if ($domainObject instanceof ObjectIdentityInterface) {
            $domainObject = $domainObject->getType();
        }

        if (is_object($domainObject)) {
            $domainObject = get_class($domainObject);
        }

        if (!is_string($domainObject)) {
            throw new InvalidArgumentException('The domain object must be an string for "class" type');
        }

        return ClassUtils::getRealClass($domainObject);
    }
}
