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
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * The Object ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Object extends AbstractRuleDefinition
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
        return 'object';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array(static::TYPE_OBJECT);
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

        // force not found acl
        if ('class' === $oid->getIdentifier()) {
            $oid = new ObjectIdentity('object', $oid->getType());
        }

        return $this->am->doIsGranted($sids, $masks, $oid, $initOid, $field);
    }
}
