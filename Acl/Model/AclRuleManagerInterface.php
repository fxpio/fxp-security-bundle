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
     * Set disabled rule.
     *
     * @param string $rule
     *
     * @return AclRuleManagerInterface
     */
    public function setDisabledRule($rule);

    /**
     * Get disabled rule.
     *
     * @return string
     */
    public function getDisabledRule();

    /**
     * Check if rule manager return the disabled rule definition or the
     * definition defined in configuration.
     *
     * @return boolean True the disabled rule, False the rule defined in config
     */
    public function isDisabled();

    /**
     * Force rule manager to return the rule definition defined in configuration.
     *
     * @return AclRuleManagerInterface
     */
    public function enable();

    /**
     * Force rule manager to return the disabled rule definition.
     *
     * @return AclRuleManagerInterface
     */
    public function disable();

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
     * Return the disabled rule name if the rule manager is disabled.
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
     * @return RuleDefinitionInterface
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

    /**
     * Get the acl rule filter definition.
     *
     * @param string $name
     * @param string $type
     *
     * @return RuleFilterDefinitionInterface
     */
    public function getFilterDefinition($name, $type);

    /**
     * Check if the acl rule filter definition exist.
     *
     * @param string $name
     * @param string $type
     *
     * @return boolean
     */
    public function hasFilterDefinition($name, $type);
}
