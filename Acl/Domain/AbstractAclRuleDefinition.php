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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Abstract class for Acl Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAclRuleDefinition implements AclRuleDefinitionInterface
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
    public function isGranted(AclRuleContextInterface $arc, ObjectIdentityInterface $oid, array $masks, $field = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextInterface $arc, EntityManager $em, ClassMetadata $targetEntity, $targetTableAlias)
    {
        return '';
    }
}
