<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection;

use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ACL rule extension for add the rule definitions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RuleExtension implements RuleExtensionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $definitionServiceIds;

    /**
     * @var array
     */
    protected $filterDefinitionServiceIds;

    /**
     * @var array
     */
    protected $cacheDefinition;

    /**
     * @var array
     */
    protected $cacheFilterDefinition;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $definitionServiceIds
     * @param array              $filterDefinitionServiceIds
     */
    public function __construct(ContainerInterface $container,
            array $definitionServiceIds,
            array $filterDefinitionServiceIds)
    {
        $this->container = $container;
        $this->definitionServiceIds = $definitionServiceIds;
        $this->filterDefinitionServiceIds = $filterDefinitionServiceIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (isset($this->cacheDefinition[$name])) {
            return $this->cacheDefinition[$name];
        }

        if (!isset($this->definitionServiceIds[$name])) {
            throw new InvalidArgumentException(sprintf('The rule definition "%s" is not registered with the service container.', $name));
        }

        $definition = $this->container->get($this->definitionServiceIds[$name]);

        if ($definition->getName() !== $name) {
            throw new InvalidArgumentException(
                    sprintf('The rule definition name specified for the service "%s" does not match the actual name. Expected "%s", given "%s"',
                            $this->definitionServiceIds[$name],
                            $name,
                            $definition->getName()
                    ));
        }

        $this->cacheDefinition[$name] = $definition;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefinition($name)
    {
        return isset($this->definitionServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterDefinition($name, $type)
    {
        if (isset($this->cacheFilterDefinition[$type.':'.$name])) {
            return $this->cacheFilterDefinition[$type.':'.$name];
        }

        if (!isset($this->filterDefinitionServiceIds[$type.':'.$name])) {
            throw new InvalidArgumentException(sprintf('The rule filter definition "%s" with "%s" type is not registered with the service container.', $name, $type));
        }

        $definition = $this->container->get($this->filterDefinitionServiceIds[$type.':'.$name]);

        if ($definition->getName() !== $name && $definition->getType() !== $type) {
            throw new InvalidArgumentException(
                    sprintf('The rule filter  definition name specified for the service "%s" does not match the actual name. Expected "%s" ("%s"), given "%s" ("%s")',
                            $this->filterDefinitionServiceIds[$name],
                            $name,
                            $type,
                            $definition->getName(),
                            $definition->getType()
                    ));
        }

        if (!$this->hasDefinition($definition->getName())) {
            throw new InvalidArgumentException(
                    sprintf('The filter definition "%s" is associated with unexisting rule definition',
                            $definition->getName()
                    ));
        }

        $this->cacheFilterDefinition[$type.':'.$name] = $definition;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilterDefinition($name, $type)
    {
        return isset($this->filterDefinitionServiceIds[$type.':'.$name]);
    }
}
