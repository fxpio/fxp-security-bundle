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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextOrmFilterInterface;

/**
 * Abstract class for Acl Rule Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAclRuleDefinition implements AclRuleDefinitionInterface
{
    /**
     * @var AclRuleManagerInterface
     */
    protected $arm;

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array(static::TYPE_SKIP_OPTIMIZATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setAclRuleManager(AclRuleManagerInterface $arm)
    {
        $this->arm = $arm;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRuleManager()
    {
        return $this->arm;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AclRuleContextDefinitionInterface $arc)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(AclRuleContextOrmFilterInterface $arc)
    {
        return '';
    }
}
