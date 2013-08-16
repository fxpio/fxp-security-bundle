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

/**
 * Adds all services with the tags "sonatra_security.acl.object_filter_voter" as arguments
 * of the "sonatra_security.acl.object_filter_extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclObjectFilterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra_security.acl.object_filter_extension')) {
            return;
        }

        $voters = array();
        foreach ($container->findTaggedServiceIds('sonatra_security.acl.object_filter_voter') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $voters[$priority][] = $id;
        }

        if (empty($voters)) {
            return;
        }

        // sort by priority and flatten
        krsort($voters);
        $voters = call_user_func_array('array_merge', $voters);

        $container->getDefinition('sonatra_security.acl.object_filter_extension')->replaceArgument(1, $voters);
    }
}
