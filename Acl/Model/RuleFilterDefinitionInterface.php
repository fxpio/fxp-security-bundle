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
 * Acl Rule Filter Definition Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RuleFilterDefinitionInterface
{
    /**
     * Returns the name of this filter definition.
     *
     * @return string The name of this filter definition
     */
    public function getName();

    /**
     * Returns the type of this filter definition.
     *
     * @return string The type of filter definition
     */
    public function getType();
}
