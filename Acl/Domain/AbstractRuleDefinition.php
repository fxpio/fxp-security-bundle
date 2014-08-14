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

use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;

/**
 * Abstract class for Acl Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractRuleDefinition implements RuleDefinitionInterface
{
    /**
     * @var AclRuleManagerInterface
     */
    protected $arm;

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array(static::TYPE_SKIP_OPTIMIZATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setAclRuleManager(AclRuleManagerInterface $arm)
    {
        $this->arm = $arm;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRuleManager()
    {
        return $this->arm;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RuleContextDefinitionInterface $rcd)
    {
        return true;
    }
}
