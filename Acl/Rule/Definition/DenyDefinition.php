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
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

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
    public function isGranted(AclRuleContextInterface $arc, ObjectIdentityInterface $oid, array $masks, $field = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextInterface $arc, EntityManager $em, ClassMetadata $targetEntity, $targetTableAlias)
    {
        $id = 'id';
        $identifier = $targetEntity->getIdentifierFieldNames();

        if (0 < count($identifier)) {
            $id = $identifier[0];
        }

        return ' '.$targetTableAlias.'.'.$id.' = -1';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deny';
    }
}
