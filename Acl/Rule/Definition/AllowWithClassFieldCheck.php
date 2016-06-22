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
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;

/**
 * The Allow ACL Rule Definition but check the grant of class field
 * with the grant of the class.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AllowWithClassFieldCheck extends AbstractRuleDefinition
{
    /**
     * @var AclManagerInterface
     */
    protected $am;

    /**
     * @param AclManagerInterface $am
     */
    public function __construct(AclManagerInterface $am)
    {
        $this->am = $am;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'allow_with_class_field_check';
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
    public function isGranted(RuleContextDefinitionInterface $rcd)
    {
        if (null !== $rcd->getField()) {
            $sids = $rcd->getSecurityIdentities();
            $oid = $rcd->getObjectIdentity();
            $initOid = $oid;
            $masks = $rcd->getMasks();

            return $this->am->doIsGranted($sids, $masks, $oid, $initOid);
        }

        return true;
    }
}
