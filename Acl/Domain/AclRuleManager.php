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

use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection\RuleExtensionInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * ACL Rule Manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclRuleManager implements AclRuleManagerInterface
{
    /**
     * @var RuleExtensionInterface
     */
    protected $ruleExtension;

    /**
     * @var string
     */
    protected $defaultRule;

    /**
     * @var array
     */
    protected $rules;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Constructor.
     *
     * @param RuleExtensionInterface $ruleExtension
     * @param array                  $rules
     * @param string                 $defaultRule
     */
    public function __construct(RuleExtensionInterface $ruleExtension,
            $defaultRule,
            array $rules = array())
    {
        $this->ruleExtension = $ruleExtension;
        $this->rules = $rules;
        $this->defaultRule = $defaultRule;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultRule($rule)
    {
        $this->defaultRule = $rule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultRule()
    {
        return $this->defaultRule;
    }

    /**
     * {@inheritdoc}
     */
    public function setRule($rule, $type, $classname, $fieldname = null)
    {
        $classname = ClassUtils::getRealClass($classname);
        $rule = $this->validateRuleName($rule);
        $type = $this->validateTypeName($type);
        $cacheName = strtolower("$type::$classname:$fieldname");

        $this->cache[$cacheName] = $rule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule($type, $classname, $fieldname = null)
    {
        $classname = ClassUtils::getRealClass($classname);
        $cacheName = strtolower("$type::$classname:$fieldname");

        if (isset($this->cache[$cacheName])) {
            return $this->cache[$cacheName];
        }

        $type = $this->validateTypeName($type);
        $rule = null;

        if (null !== $fieldname
                && isset($this->rules[$classname]['fields'][$fieldname]['rules'][$type])) {
            $rule = $this->rules[$classname]['fields'][$fieldname]['rules'][$type];
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && null !== $fieldname
                && isset($this->rules[$classname]['fields'][$fieldname]['rules'])) {
            $rule = $this->getParentRule($type, $this->rules[$classname]['fields'][$fieldname]['rules']);
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && null !== $fieldname
                && isset($this->rules[$classname]['fields'][$fieldname]['default'])) {
            $rule = $this->rules[$classname]['fields'][$fieldname]['default'];
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && null !== $fieldname
                && isset($this->rules[$classname]['default_fields'])) {
            $rule = $this->rules[$classname]['default_fields'];
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && isset($this->rules[$classname]['rules'][$type])) {
            $rule = $this->rules[$classname]['rules'][$type];
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && isset($this->rules[$classname]['rules'])) {
            $rule = $this->getParentRule($type, $this->rules[$classname]['rules']);
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule
                && isset($this->rules[$classname]['default'])) {
            $rule = $this->rules[$classname]['default'];
            $rule = $rule !== '' ? $rule : null;
        }

        if (null === $rule) {
            $rule = $this->defaultRule;
        }

        //save in cache and return value of cache
        $this->setRule($rule, $type, $classname, $fieldname);

        return $this->getRule($type, $classname, $fieldname);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        return $this->ruleExtension->getDefinition($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefinition($name)
    {
        return $this->ruleExtension->hasDefinition($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterDefinition($name, $type)
    {
        return $this->ruleExtension->getFilterDefinition($name, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilterDefinition($name, $type)
    {
        return $this->ruleExtension->hasFilterDefinition($name, $type);
    }

    /**
     * Validate the rule name with existing constant.
     *
     * @param string $rule
     *
     * @return string
     */
    protected function validateRuleName($rule)
    {
        if (!$this->hasDefinition($rule)) {
            throw new SecurityException(sprintf('The rule "%s" in configuration of Sonatra ACL Rules does not exist', $rule));
        }

        return $rule;
    }

    /**
     * Validate the type name with existing constant.
     *
     * @param string $type
     *
     * @return string
     */
    protected function validateTypeName($type)
    {
        $type = strtoupper($type);

        if (!defined('Symfony\Component\Security\Acl\Permission\MaskBuilder::MASK_'.$type)) {
            throw new SecurityException(sprintf('The type "%s" in configuration of Sonatra ACL Rules does not exist', $type));
        }

        return $type;
    }

    /**
     * Get the parent decision rule.
     *
     * @param string $type
     * @param array  $rules
     *
     * @return string|null
     */
    protected function getParentRule($type, array $rules)
    {
        $pRules = $this->getParentRules($type);

        foreach ($pRules as $i => $pRule) {
            if (isset($rules[$pRule])) {
                return $rules[$pRule];
            }
        }

        return null;
    }

    /**
     * Get the list of parent desicion rules.
     *
     * @param string $type
     *
     * @return array
     */
    protected function getParentRules($type)
    {
        $type = strtoupper($type);
        $rules = array($type);

        switch ($type) {
            case 'VIEW':
                $rules = array(
                    'VIEW',
                    'EDIT',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'EDIT':
                $rules = array(
                    'EDIT',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'CREATE':
                $rules = array(
                    'CREATE',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'DELETE':
                $rules = array(
                    'DELETE',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'UNDELETE':
                $rules = array(
                    'UNDELETE',
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'OPERATOR':
                $rules = array(
                    'OPERATOR',
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'MASTER':
                $rules = array(
                    'MASTER',
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'OWNER':
                $rules = array(
                    'OWNER',
                    'IDDQD',
                );
                break;

            case 'IDDQD':
                $rules = array(
                    'IDDQD',
                );
                break;
        }

        return $rules;
    }
}
