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

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Acl Rule Definition Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclRuleDefinitionInterface
{
    /**
     * Returns the name of this definition.
     *
     * @return string The name of this definition
     */
    public function getName();

    /**
     * Check if identity is granted on ACL Manager.
     *
     * @param AclRuleContextInterface $arc
     * @param mixed                   $domainObject
     * @param array                   $masks
     * @param string                  $field
     *
     * @return boolean
     */
    public function isGranted(AclRuleContextInterface $arc, $domainObject, array $masks, $field = null);

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
