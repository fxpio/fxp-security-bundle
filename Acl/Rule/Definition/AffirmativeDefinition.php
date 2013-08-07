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

use Sonatra\Bundle\SecurityBundle\Acl\Domain\AbstractAclRuleDefinition;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextOrmFilterInterface;

/**
 * The Affirmative ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AffirmativeDefinition extends AbstractAclRuleDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'affirmative';
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
    public function isGranted(AclRuleContextDefinitionInterface $arc)
    {
        $oDef = $this->arm->getDefinition('object');
        $cDef = $this->arm->getDefinition('class');

        return $oDef->isGranted($arc)
                || $cDef->isGranted($arc);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextOrmFilterInterface $arc)
    {
        if (0 === count($arc->getSecurityIdentities())) {
            return '';
        }

        $oDef = $this->arm->getDefinition('object');
        $cDef = $this->arm->getDefinition('class');

        $oFilter = $oDef->addFilterConstraint($arc);
        $cFilter = $cDef->addFilterConstraint($arc);

        if ('' !== $oFilter && '' !== $cFilter) {
            return " (".$oFilter.") OR (".$cFilter.")";

        } elseif ('' === $oFilter && '' !== $cFilter) {
            return ' '.$cFilter;

        } elseif ('' !== $oFilter && '' === $cFilter) {
            return ' '.$oFilter;
        }

        return '';
    }
}
