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
     * Set acl rule manager.
     *
     * @param AclRuleManagerInterface $arm
     */
    public function setAclRuleManager(AclRuleManagerInterface $arm);

    /**
     * Get acl rule manager.
     *
     * @return AclRuleManagerInterface
     */
    public function getAclRuleManager();

    /**
     * Check if identity is granted on ACL Manager.
     *
     * @param AclRuleContextInterface $arc
     *
     * @return boolean
     */
    public function isGranted(AclRuleContextDefinitionInterface $arc);

    /**
     * Add Doctrine ORM SQL Filter Constraint.
     *
     * @param AclRuleContextOrmFilterInterface $arc
     *
     * @return string
     */
    public function addFilterConstraint(AclRuleContextOrmFilterInterface $arc);
}
