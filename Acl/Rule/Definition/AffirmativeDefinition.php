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
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

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
    public function isGranted(AclRuleContextInterface $arc, $domainObject, array $masks, $field = null)
    {
        $am = $arc->getAclManager();
        $arm = $arc->getAclRuleManager();
        $securityIdentities = $arc->getSecurityIdentities();
        $oDef = $arm->getDefinition('object');
        $cDef = $arm->getDefinition('class');

        return $cDef->isGranted($arc, $domainObject, $masks, $field)
                || $oDef->isGranted($arc, $domainObject, $masks, $field);
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
            return " (".$oFilter.") OR (".$cFilter.")";

        } elseif ('' === $oFilter && '' !== $cFilter) {
            return ' '.$cFilter;

        } elseif ('' !== $oFilter && '' === $cFilter) {
            return ' '.$oFilter;
        }

        return '';
    }
}
