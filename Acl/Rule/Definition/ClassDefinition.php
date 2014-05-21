<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Rule\Definition;

use Sonatra\Bundle\SecurityBundle\Acl\Domain\AbstractRuleDefinition;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleContextDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;

/**
 * The Class ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ClassDefinition extends AbstractRuleDefinition
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
        return 'class';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array(static::TYPE_CLASS);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RuleContextDefinitionInterface $rcd)
    {
        $sids = $rcd->getSecurityIdentities();
        $oid = $rcd->getObjectIdentity();
        $initOid = $oid;
        $field = $rcd->getField();
        $masks = $rcd->getMasks();

        if ('class' !== $oid->getType()) {
            $oid = $this->am->createClassObjectIdentity($oid->getType());
        }

        return $this->am->doIsGranted($sids, $masks, $oid, $initOid, $field);
    }
}
