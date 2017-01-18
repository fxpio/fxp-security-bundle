<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVariableStoragePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra_security.expression.variable_storage')) {
            return;
        }

        $variables = array();
        foreach ($container->findTaggedServiceIds('sonatra_security.expression.variables') as $id => $tags) {
            foreach ($tags as $attributes) {
                foreach ($attributes as $name => $value) {
                    $value = $this->buildValue($container, $id, $value);

                    if (null !== $value) {
                        $variables[$name] = $value;
                    }
                }
            }
        }

        $container->getDefinition('sonatra_security.expression.variable_storage')->replaceArgument(0, $variables);
    }

    /**
     * Build the value of the expression variables.
     *
     * @param ContainerBuilder $container The container
     * @param string           $serviceId The service id
     * @param mixed            $value     The value of expression variables
     *
     * @return mixed
     */
    private function buildValue(ContainerBuilder $container, $serviceId, $value)
    {
        if (is_string($value) && 0 === strpos($value, '@')) {
            $value = ltrim($value, '@');
            $optional = 0 === strpos($value, '?');
            $value = ltrim($value, '?');
            $hasDef = $container->hasDefinition($value);

            if (!$hasDef && !$optional) {
                throw new ServiceNotFoundException($value, $serviceId);
            }

            $value = $hasDef
                ? new Reference($value)
                : null;
        }

        return $value;
    }
}
