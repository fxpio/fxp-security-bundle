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
use Sonatra\Bundle\SecurityBundle\Acl\Domain\RuleContextDefinition;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;

/**
 * The Parent ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ParentDefinition extends AbstractRuleDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'parent';
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
        if (null === $rcd->getField()) {
            throw new \InvalidArgumentException('The rule definition "parent" must be only associated with class field');
        }

        $rcd = new RuleContextDefinition($rcd->getSecurityIdentities(), $rcd->getObjectIdentity(), $rcd->getMasks());
        $type = AclUtils::convertToAclName($rcd->getMasks()[0]);
        $defName = $this->arm->getRule($type[0], $rcd->getObjectIdentity()->getType());
        $def = $this->arm->getDefinition($defName);

        return $def->isGranted($rcd);
    }
}
