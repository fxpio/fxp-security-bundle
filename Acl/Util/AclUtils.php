<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Util;

use Sonatra\Bundle\SecurityBundle\Acl\Domain\GroupSecurityIdentity;
use Sonatra\Bundle\SecurityBundle\Acl\Domain\OrganizationSecurityIdentity;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\RuntimeException;
use Sonatra\Bundle\SecurityBundle\Model\GroupInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Class related functionality for acl manipulation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclUtils
{
    /**
     * @var string
     */
    private static $permissionMapClass = BasicPermissionMap::class;

    /**
     * @var array<string, string>|null
     */
    private static $permissionMap;

    /**
     * @var BasicPermissionMap|null
     */
    private static $permissionMapInstance;

    /**
     * @var array|null
     */
    private static $permissionMapRules;

    /**
     * Set the class name of permission map.
     *
     * @param string $class The class name
     */
    public static function setPermissionMapClass($class)
    {
        static::$permissionMapClass = $class;
        static::$permissionMap = null;
        static::$permissionMapInstance = null;
        static::$permissionMapRules = null;
    }

    /**
     * Get the map of mask builder code and permission name.
     *
     * @return array
     */
    public static function getPermissionMap()
    {
        if (null === static::$permissionMap) {
            static::$permissionMap = array();
            $refPerm = new \ReflectionClass(static::$permissionMapClass);
            $refMask = new \ReflectionClass(static::getPermissionMapInstance()->getMaskBuilder());

            foreach ($refMask->getConstants() as $name => $cMask) {
                $subName = substr($name, 5);

                if (0 === strpos($name, 'MASK_')
                        && defined($cName = $refMask->getName().'::CODE_'.$subName)
                        && defined($pName = $refPerm->getName().'::PERMISSION_'.$subName)) {
                    static::$permissionMap[constant($cName)] = constant($pName);
                }
            }
        }

        return static::$permissionMap;
    }

    /**
     * Convert the acl name or the array of acl name to mask.
     *
     * @param int|string|array $mask
     *
     * @return int
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
        $builder = static::getPermissionMapInstance()->getMaskBuilder();
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
            throw new InvalidArgumentException('The mask must be a int');
        }

        $mb = static::getPermissionMapInstance()->getMaskBuilder();
        $mb->set($mask);
        $pattern = $mb->getPattern();
        $rights = array();

        foreach (static::getPermissionMap() as $code => $permission) {
            if (false !== strpos($pattern, $code)) {
                $rights[] = $permission;
            }
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
            } elseif ($identity instanceof OrganizationInterface) {
                $sids[] = OrganizationSecurityIdentity::fromAccount($identity);
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
                $str = 'Identity must implement one of: RoleInterface, UserInterface, GroupInterface, OrganizationInterface or string';

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

    /**
     * Get the list of parent decision rules.
     *
     * @param string $type
     *
     * @return string[]
     */
    public static function getParentRules($type)
    {
        $type = strtoupper($type);
        $rules = array($type);
        $mapRules = static::getPermissionMapRules();

        if (isset($mapRules[$type])) {
            $rules = $mapRules[$type];
        }

        return $rules;
    }

    /**
     * Get the class name of mask builder.
     *
     * @return string
     */
    public static function getMaskBuilderClass()
    {
        return get_class(static::getPermissionMapInstance()->getMaskBuilder());
    }

    /**
     * Create the instance of permission map.
     *
     * @return BasicPermissionMap
     */
    public static function createPermissionMapInstance()
    {
        $permission = new static::$permissionMapClass();

        if (!$permission instanceof BasicPermissionMap) {
            throw new RuntimeException('The permission map class must be an instance of BasicPermissionMap');
        }

        return $permission;
    }

    /**
     * Get the permission map rules.
     *
     * @return array
     */
    private static function getPermissionMapRules()
    {
        if (null === static::$permissionMapRules) {
            $pm = static::getPermissionMapInstance();
            $ref = new \ReflectionClass($pm);
            $prop = $ref->getProperty('map');
            $prop->setAccessible(true);
            static::$permissionMapRules = $prop->getValue($pm);

            foreach (static::$permissionMapRules as &$rules) {
                foreach ($rules as $i => &$mask) {
                    $mask = current(static::convertToAclName($mask));
                }
            }
        }

        return static::$permissionMapRules;
    }

    /**
     * Get the instance of permission map.
     *
     * @return BasicPermissionMap
     */
    private static function getPermissionMapInstance()
    {
        if (null === static::$permissionMapInstance) {
            static::$permissionMapInstance = static::createPermissionMapInstance();
        }

        return static::$permissionMapInstance;
    }
}
