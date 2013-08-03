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

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Acl Manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclManagerInterface
{
    /**
     * Get the security identities of token.
     *
     * @param TokenInterface $token
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities(TokenInterface $token = null);

    /**
     * Retrieves the object identity from a domain object.
     *
     * @param object $domainObject
     *
     * @return ObjectIdentityInterface
     */
    public function getObjectIdentity($domainObject);

    /**
     * Retrieves the object identities from domain objects.
     *
     * @param object[] $domainObjects
     *
     * @return ObjectIdentityInterface[]
     */
    public function getObjectIdentities(array $domainObjects);

    /**
     * Determines whether access is granted.
     *
     * @param RoleInterface[]|UserInterface[]|TokenInterface[]|string[]|SecurityIdentityInterface[] $sids
     * @param DomainObjectInterface|object|string                                                   $domainObject
     * @param int|string|array                                                                      $mask
     *
     * @return boolean
     */
    public function isGranted($sids, $domainObject, $mask);

    /**
     * Determines whether field access is granted. Alias of isGranted with
     * FieldVote class for Domain Object.
     *
     * @param RoleInterface[]|UserInterface[]|TokenInterface[]|string[]|SecurityIdentityInterface[] $sids
     * @param DomainObjectInterface|object|string                                                   $domainObject
     * @param string                                                                                $field
     * @param int|string|array                                                                      $mask
     *
     * @return boolean
     */
    public function isFieldGranted($sids, $domainObject, $field, $mask);

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
     * @param string|int                          $type         The mask type
     * @param DomainObjectInterface|object|string $domainObject The domainObject
     * @param string                              $field        The field name
     *
     * @return string
     */
    public function getRule($type, $domainObject, $field = null);

    /**
     * Check if the security identities are granted on object identity.
     * Used in isGranted() by ACL Rule Definition.
     *
     * @param SecurityIdentityInterface[] $sids
     * @param array                       $masks
     * @param ObjectIdentityInterface     $oid
     * @param string                      $field
     */
    public function doIsGranted(array $sids, array $masks, ObjectIdentityInterface $oid, $field = null);
}
