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
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;

/**
 * Abstract class for Acl Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractRuleDefinition implements RuleDefinitionInterface
{
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
    public function isGranted(RuleContextDefinitionInterface $rcd)
    {
        return true;
    }
}
