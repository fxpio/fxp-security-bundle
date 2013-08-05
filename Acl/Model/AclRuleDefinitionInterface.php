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
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Acl Rule Definition Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclRuleDefinitionInterface
{
    const TYPE_CLASS             = 'class';
    const TYPE_OBJECT            = 'object';
    const TYPE_SKIP_OPTIMIZATION = 'skip';

    /**
     * Returns the name of this definition.
     *
     * @return string The name of this definition
     */
    public function getName();

    /**
     * Returns the prelaod type of this definition.
     *
     * @return array The preload types of this definition
     */
    public function getTypes();

    /**
     * Check if identity is granted on ACL Manager.
     *
     * @param AclRuleContextInterface $arc
     * @param ObjectIdentityInterface $oid
     * @param array                   $masks
     * @param string                  $field
     *
     * @return boolean
     */
    public function isGranted(AclRuleContextInterface $arc, ObjectIdentityInterface $oid, array $masks, $field = null);

    /**
     * Add Doctrine ORM SQL Filter Constraint.
     *
     * @param AclRuleContextInterface $arc
     * @param EntityManager           $em
     * @param ClassMetadata           $targetEntity
     * @param string                  $targetTableAlias
     *
     * @return string
     */
    public function addFilterConstraint(AclRuleContextInterface $arc, EntityManager $em, ClassMetadata $targetEntity, $targetTableAlias);
}
