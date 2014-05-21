<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

/**
 * Acl Rule Context Doctrine ORM Filter Definition Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrmFilterRuleContextDefinitionInterface extends RuleContextInterface
{
    /**
     * Get the target entity class metadata.
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getTargetEntity();

    /**
     * Get the target table alias name.
     *
     * @return string
     */
    public function getTargetTableAlias();
}
