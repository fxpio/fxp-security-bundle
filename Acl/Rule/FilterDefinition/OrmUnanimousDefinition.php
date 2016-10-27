<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Rule\FilterDefinition;

use Sonatra\Bundle\SecurityBundle\Acl\Domain\AbstractRuleOrmFilterDefinition;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\OrmFilterRuleContextDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleOrmFilterDefinitionInterface;

/**
 * The Unanimous ACL Rule Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrmUnanimousDefinition extends AbstractRuleOrmFilterDefinition
{
    /**
     * @var AclRuleManagerInterface
     */
    protected $arm;

    /**
     * Constructor.
     *
     * @param AclRuleManagerInterface $arm The ACL rule manager
     */
    public function __construct(AclRuleManagerInterface $arm)
    {
        $this->arm = $arm;
    }

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
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd)
    {
        if (0 === count($rcd->getSecurityIdentities())) {
            return '';
        }

        /* @var RuleOrmFilterDefinitionInterface $oDef */
        $oDef = $this->arm->getFilterDefinition('object', $this->getType());
        /* @var RuleOrmFilterDefinitionInterface $cDef */
        $cDef = $this->arm->getFilterDefinition('class', $this->getType());

        $oFilter = $oDef->addFilterConstraint($rcd);
        $cFilter = $cDef->addFilterConstraint($rcd);

        if ('' !== $oFilter && '' !== $cFilter) {
            return ' ('.$oFilter.') AND ('.$cFilter.')';
        } elseif ('' === $oFilter && '' !== $cFilter) {
            return $cFilter;
        } elseif ('' !== $oFilter && '' === $cFilter) {
            return $oFilter;
        }

        return '';
    }
}
