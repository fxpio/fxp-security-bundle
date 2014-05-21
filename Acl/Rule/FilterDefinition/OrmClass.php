<?php

/**
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
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The Class ACL Rule Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrmClass extends AbstractRuleOrmFilterDefinition
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param EntityManagerInterface $em
     * @param                        $options
     */
    public function __construct(EntityManagerInterface $em, array $options)
    {
        $this->em = $em;
        $this->options = $options;
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
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd)
    {
        $identities = $rcd->getSecurityIdentities();

        if (0 === count($identities)) {
            return '';
        }

        $connection = $this->em->getConnection();
        $classname = $connection->quote($rcd->getTargetEntity()->getName());
        $sids = array();

        /* @var RoleSecurityIdentity $sid */
        foreach ($identities as $sid) {
            if ($sid instanceof UserSecurityIdentity) {
                /* @var UserSecurityIdentity $sid */
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
            {$this->options['entry_table_name']} e
        JOIN
            {$this->options['oid_table_name']} oid ON (
                oid.class_id = e.class_id
                AND oid.object_identifier = 'class'
            )
        JOIN
            {$this->options['sid_table_name']} s ON (
                s.id = e.security_identity_id
            )
        JOIN
            {$this->options['class_table_name']} class ON (
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
