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
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The Object ACL Rule Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrmObject extends AbstractRuleOrmFilterDefinition
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd)
    {
        $identities = $rcd->getSecurityIdentities();

        if (0 === count($identities)) {
            return '';
        }

        $connection = $this->em->getConnection();
        $classname = $connection->quote($rcd->getTargetEntity()->getName());
        $tableAlias = $rcd->getTargetTableAlias();
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
            oid.object_identifier
        FROM
            acl_entries e
        JOIN
            acl_object_identities oid ON (
                oid.class_id = e.class_id
                AND oid.object_identifier != 'class'
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
            {$connection->getDatabasePlatform()->getIsNotNullExpression('e.object_identity_id')}
            AND (e.mask in (4,6,12,16,20,30) OR e.mask >= 32 OR ((e.mask / 2) % 1) > 0)
            AND oid.id = e.object_identity_id
            AND {$sids}
            AND class.class_type = {$classname}
        GROUP BY
            oid.object_identifier
SELECTCLAUSE;

        return " $tableAlias.id IN (".$sql.")";
    }
}
