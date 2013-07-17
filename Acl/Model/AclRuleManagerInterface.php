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
 * Acl Rule Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclRuleManagerInterface
{
    /**
     * Set default rule.
     *
     * @param string $rule
     *
     * @return AclRuleManagerInterface
     */
    public function setDefaultRule($rule);

    /**
     * Get default rule.
     *
     * @return string
     */
    public function getDefaultRule();

    /**
     * Set rule for classname/field classname.
     *
     * @param string $rule      The acl rule
     * @param string $type      The acl mask name (VIEW, etc...)
     * @param string $classname The class name
     * @param string $fieldname The field name
     *
     * @return AclRuleManagerInterface
     */
    public function setRule($rule, $type, $classname, $fieldname = null);

    /**
     * Get rule for classname/field classname with Pre-Authorization Decisions
     * of Syfmony2 Advanced ACL Documentation.
     *
     * @param string $type      The acl mask name (VIEW, etc...)
     * @param string $classname The class name
     * @param string $fieldname The field name
     *
     * @return string
     */
    public function getRule($type, $classname, $fieldname = null);

    /**
     * Get the acl rule definition.
     *
     * @param string $name
     *
     * @return AclRuleDefinitionInterface
     */
    public function getDefinition($name);

    /**
     * Check if the acl rule definition exist.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasDefinition($name);
}
