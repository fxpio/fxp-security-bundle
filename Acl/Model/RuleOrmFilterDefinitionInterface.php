<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

/**
 * Acl Rule Filter Definition Interface for Doctrine ORM Filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RuleOrmFilterDefinitionInterface extends RuleFilterDefinitionInterface
{
    /**
     * Add Doctrine ORM SQL Filter Constraint.
     *
     * @param OrmFilterRuleContextDefinitionInterface $rcd
     *
     * @return string
     */
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd);
}
