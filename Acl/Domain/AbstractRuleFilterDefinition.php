<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleFilterDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;

/**
 * Abstract class for Acl Rule Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractRuleFilterDefinition implements RuleFilterDefinitionInterface
{
    /**
     * @var AclRuleManagerInterface
     */
    protected $arm;

    /**
     * {@inheritdoc}
     */
    public function setAclRuleManager(AclRuleManagerInterface $arm)
    {
        $this->arm = $arm;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRuleManager()
    {
        return $this->arm;
    }
}
