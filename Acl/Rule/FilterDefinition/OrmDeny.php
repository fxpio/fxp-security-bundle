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
use Sonatra\Bundle\SecurityBundle\Acl\Model\OrmFilterRuleContextDefinitionInterface;

/**
 * The Deny ACL Rule Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrmDeny extends AbstractRuleOrmFilterDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deny';
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd)
    {
        $id = 'id';
        $identifier = $rcd->getTargetEntity()->getIdentifierFieldNames();

        if (0 < count($identifier)) {
            $id = $identifier[0];
        }

        return ' '.$rcd->getTargetTableAlias().'.'.$id.' = -1';
    }
}
