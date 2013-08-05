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
 * The Unanimous ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UnanimousDefinition extends AbstractAclRuleDefinition
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
    public function isGranted(AclRuleContextInterface $arc, ObjectIdentityInterface $oid, array $masks, $field = null)
    {
        $arm = $arc->getAclRuleManager();
        $oDef = $arm->getDefinition('object');
        $cDef = $arm->getDefinition('class');

        return $oDef->isGranted($arc, $oid, $masks, $field)
                && $cDef->isGranted($arc, $oid, $masks, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextInterface $arc, EntityManager $em, ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (0 === count($arc->getSecurityIdentities())) {
            return '';
        }

        $oDef = $arc->getAclRuleManager()->getDefinition('object');
        $cDef = $arc->getAclRuleManager()->getDefinition('class');

        $oFilter = $oDef->addFilterConstraint($arc, $em, $targetEntity, $targetTableAlias);
        $cFilter = $cDef->addFilterConstraint($arc, $em, $targetEntity, $targetTableAlias);

        if ('' !== $oFilter && '' !== $cFilter) {
            return " (".$oFilter.") AND (".$cFilter.")";

        } elseif ('' === $oFilter && '' !== $cFilter) {
            return $cFilter;

        } elseif ('' !== $oFilter && '' === $cFilter) {
            return $oFilter;
        }

        return '';
    }
}
