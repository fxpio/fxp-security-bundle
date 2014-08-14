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

use Sonatra\Bundle\SecurityBundle\Acl\Model\OrmFilterRuleContextDefinitionInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Class for Acl Rule Context Doctrine ORM Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrmFilterRuleContextDefinition extends AbstractRuleContext implements OrmFilterRuleContextDefinitionInterface
{
    /**
     * @var ClassMetadata
     */
    protected $targetEntity;

    /**
     * @var string
     */
    protected $targetTableAlias;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface[] $sids
     * @param ClassMetadata               $targetEntity
     * @param string                      $targetTableAlias
     */
    public function __construct(array $sids, ClassMetadata $targetEntity, $targetTableAlias)
    {
        parent::__construct($sids);

        $this->targetEntity = $targetEntity;
        $this->targetTableAlias = $targetTableAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetTableAlias()
    {
        return $this->targetTableAlias;
    }
}
