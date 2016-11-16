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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds all services with the tags "sonatra_security.object_filter.voter" as arguments
 * of the "sonatra_security.object_filter.extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra_security.object_filter.extension')) {
            return;
        }

        $voters = array();
        foreach ($container->findTaggedServiceIds('sonatra_security.object_filter.voter') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $voters[$priority][] = new Reference($id);
        }

        // sort by priority and flatten
        krsort($voters);
        $voters = call_user_func_array('array_merge', $voters);

        $container->getDefinition('sonatra_security.object_filter.extension')->replaceArgument(0, $voters);
    }
}
