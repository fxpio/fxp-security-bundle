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
 * The Deny ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DenyDefinition extends AbstractAclRuleDefinition
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
    public function getTypes()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AclRuleContextDefinitionInterface $arc)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextOrmFilterInterface $arc)
    {
        $id = 'id';
        $identifier = $arc->getClassMetadata()->getIdentifierFieldNames();

        if (0 < count($identifier)) {
            $id = $identifier[0];
        }

        return ' '.$arc->getTableAlias().'.'.$id.' = -1';
    }
}
