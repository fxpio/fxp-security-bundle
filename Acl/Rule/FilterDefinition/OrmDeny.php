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
use Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Util\DoctrineUtils;

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
        $targetEntity = $rcd->getTargetEntity();
        $id = DoctrineUtils::getIdentifier($targetEntity);

        return ' '.$rcd->getTargetTableAlias().'.'.$id.' = '.DoctrineUtils::getMockZeroId($targetEntity);
    }
}
