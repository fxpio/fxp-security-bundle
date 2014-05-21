<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acl Manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclManagerInterface
{
    /**
     * Check if acl manager is disabled.
     *
     * If the acl manager is disabled, all asked authorizations will be
     * always accepted.
     *
     * If the acl manager is enabled, all asked authorizations will be accepted
     * depending on the acl rules.
     *
     * @return boolean
     */
    public function isDisabled();

    /**
     * Enables the acl manager (the asked authorizations will be accepted
     * depending on the acl rules).
     *
     * @return AclManagerInterface
     */
    public function enable();

    /**
     * Disables the acl manager (the asked authorizations will be always
     * accepted).
     *
     * @return AclManagerInterface
     */
    public function disable();

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
     * Create class object identity.
     * The Object Identity of Class created is cached for always obtain the
     * same instance anywhere.
     *
     * @param string $type
     *
     * @param ObjectIdentityInterface
     */
    public function createClassObjectIdentity($type);

    /**
     * Get the all preload types (class and field) of class.
     *
     * @param FieldVote|ObjectIdentity|object|string $domainObject
     *
     * @return array The list of preload type
     */
    public function getPreloadTypes($domainObject);

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
     * @return \SplObjectStorage
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
     * Get the internal acl rules.
     *
     * @param string|int|array $domainObject The object or classname
     * @param string           $field        The field name
     * @param array            $types        The list of acl type (empty = all types)
     *
     * @return array The map of rules
     */
    public function getRules($domainObject, $field = null, array $types = array());

    /**
     * Get all the rules for class and class fields.
     *
     * Return example:
     *   array(
     *       'class' => array(
     *           'VIEW'     => 'allow',
     *           'CREATE'   => 'allow',
     *           'EDIT'     => 'allow',
     *           'DELETE'   => 'class',
     *           'UNDELETE' => 'class',
     *           'OPERATOR' => 'class',
     *           'MASTER'   => 'class',
     *           'OWNER'    => 'class',
     *       ),
     *       'fields' => array(
     *           'name' => array(
     *               'VIEW'     => 'allow',
     *               'CREATE'   => 'allow',
     *               'EDIT'     => 'allow',
     *               'DELETE'   => 'class',
     *               'UNDELETE' => 'class',
     *               'OPERATOR' => 'class',
     *               'MASTER'   => 'class',
     *               'OWNER'    => 'class',
     *           )
     *       ),
     *   )
     *
     * @param DomainObjectInterface|object|string $domainObject The domainObject
     *
     * @return array The map contains 'class' map rules and 'fields' list map rules
     */
    public function getObjectRules($domainObject);

    /**
     * Check if the security identities are granted on object identity.
     * Used in isGranted() by ACL Rule Definition.
     *
     * @param SecurityIdentityInterface[] $sids
     * @param array                       $masks
     * @param ObjectIdentityInterface     $oid     The current object identifier
     * @param ObjectIdentityInterface     $initOid The initial object identifier
     * @param string                      $field
     */
    public function doIsGranted(array $sids, array $masks, ObjectIdentityInterface $oid, ObjectIdentityInterface $initOid, $field = null);
}
