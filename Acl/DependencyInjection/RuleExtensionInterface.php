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
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleDefinitionInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\RuleFilterDefinitionInterface;

/**
 * Interface for extensions which provide rule definitions.
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
     * @return RuleDefinitionInterface The definition
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

    /**
     * Returns a filter definition by name and type.
     *
     * @param string $name The name of the filter definition
     * @param string $type The type of the filter definition
     *
     * @return RuleFilterDefinitionInterface The filter definition
     *
     * @throws \InvalidArgumentException if the given filter definition is not supported by this extension
     * @throws \InvalidArgumentException if the given filter definition is associated with inexisting rule definition
     */
    public function getFilterDefinition($name, $type);

    /**
     * Returns whether the given filter definition is supported.
     *
     * @param string $name The name of the filter definition
     * @param string $type The type of the filter definition
     *
     * @return Boolean Whether the filter definition is supported by this extension
     */
    public function hasFilterDefinition($name, $type);
}
