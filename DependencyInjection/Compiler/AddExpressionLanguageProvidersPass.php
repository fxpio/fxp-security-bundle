<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the expression language providers.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AddExpressionLanguageProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // annotation
        if ($container->has('fxp_security.subscriber.security_annotation')) {
            $definition = $container->findDefinition('fxp_security.subscriber.security_annotation');
            foreach ($container->findTaggedServiceIds('security.expression_language_provider', true) as $id => $attributes) {
                $definition->addMethodCall('addExpressionLanguageProvider', [new Reference($id)]);
            }
        }
    }
}
