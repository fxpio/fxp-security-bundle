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

use Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection\RuleExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ACL rule extension for add the rule definitions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RuleExtension implements RuleExtensionInterface
{
    protected $container;
    protected $definitionServiceIds;
    protected $cacheDefinition;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $definitionServiceIds
     */
    public function __construct(ContainerInterface $container,
            array $definitionServiceIds)
    {
        $this->container = $container;
        $this->definitionServiceIds = $definitionServiceIds;
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
            throw new \InvalidArgumentException(sprintf('The rule definition "%s" is not registered with the service container.', $name));
        }

        $definition = $this->container->get($this->definitionServiceIds[$name]);

        if ($definition->getName() !== $name) {
            throw new \InvalidArgumentException(
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
}
