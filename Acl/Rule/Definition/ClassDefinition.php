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
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The Class ACL Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ClassDefinition extends AbstractAclRuleDefinition
{
    /**
     * @var AclManagerInterface
     */
    protected $am;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param AclManagerInterface $am
     */
    public function __construct(AclManagerInterface $am, EntityManagerInterface $em)
    {
        $this->am = $am;
        $this->em = $em;
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
    public function isGranted(AclRuleContextDefinitionInterface $arc)
    {
        $sids = $arc->getSecurityIdentities();
        $oid = $arc->getObjectIdentity();
        $initOid = $oid;
        $field = $arc->getField();
        $masks = $arc->getMasks();

        if ('class' !== $oid->getType()) {
            $oid = $this->am->createClassObjectIdentity($oid->getType());
        }

        return $this->am->doIsGranted($sids, $masks, $oid, $initOid, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextOrmFilterInterface $arc)
    {
        $identities = $arc->getSecurityIdentities();

        if (0 === count($identities)) {
            return '';
        }

        $connection = $this->em->getConnection();
        $classname = $connection->quote($arc->getClassMetadata()->getName());
        $sids = array();

        foreach ($identities as $sid) {
            if ($sid instanceof UserSecurityIdentity) {
                $sids[] = 's.identifier = ' . $connection->quote($sid->getClass().'-'.$sid->getUsername());
                continue;
            }

            $sids[] = 's.identifier = ' . $connection->quote($sid->getRole());
        }

        $sids =  '(' . implode(' OR ', $sids) . ')';

        $sql = <<<SELECTCLAUSE
        SELECT
            count(oid.object_identifier)
        FROM
            acl_entries e
        JOIN
            acl_object_identities oid ON (
                oid.class_id = e.class_id
                AND oid.object_identifier = 'class'
            )
        JOIN
            acl_security_identities s ON (
                s.id = e.security_identity_id
            )
        JOIN
            acl_classes class ON (
                class.id = e.class_id
            )
        WHERE
            {$connection->getDatabasePlatform()->getIsNullExpression('e.object_identity_id')}
            AND (e.mask in (4,6,12,16,20,30) OR e.mask >= 32 OR ((e.mask / 2) % 1) > 0)
            AND {$sids}
            AND class.class_type = {$classname}
       GROUP BY
            oid.object_identifier
SELECTCLAUSE;

        return " ".$sql." >= 1";
    }
}
