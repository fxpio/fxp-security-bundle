<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Rule\Definition;

use Sonatra\Bundle\SecurityBundle\Acl\Domain\AbstractRuleDefinition;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;

/**
 * The Unanimous ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Unanimous extends AbstractRuleDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'unanimous';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array(static::TYPE_CLASS, static::TYPE_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RuleContextDefinitionInterface $rcd)
    {
        $oDef = $this->arm->getDefinition('object');
        $cDef = $this->arm->getDefinition('class');

        return $oDef->isGranted($rcd)
                && $cDef->isGranted($rcd);
    }
}
