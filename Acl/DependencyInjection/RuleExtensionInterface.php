<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection;

/**
 * Interface for extensions which provide definition rules.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RuleExtensionInterface
{
    /**
     * Returns a definition by name.
     *
     * @param string $name The name of the definition
     *
     * @return AclRuleDefinitionInterface The definition
     *
     * @throws \InvalidArgumentException if the given definition is not supported by this extension
     */
    public function getDefinition($name);

    /**
     * Returns whether the given definition is supported.
     *
     * @param string $name The name of the definition
     *
     * @return Boolean Whether the definition is supported by this extension
     */
    public function hasDefinition($name);
}
