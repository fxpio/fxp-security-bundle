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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextOrmFilterInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Class for Acl Rule Context Doctrine ORM Filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclRuleContextOrmFilter extends AbstractAclRuleContext implements AclRuleContextOrmFilterInterface
{
    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * Constructor.
     *
     * @param array         $sids
     * @param ClassMetadata $classMetadata
     * @param string        $tableAlias
     */
    public function __construct(array $sids, ClassMetadata $classMetadata, $tableAlias)
    {
        parent::__construct($sids);

        $this->classMetadata = $classMetadata;
        $this->tableAlias = $tableAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }
}
