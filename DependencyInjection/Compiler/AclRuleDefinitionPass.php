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
 * Adds all services with the tags "sonatra.acl.rule_definition" as arguments
 * of the "sonatra.acl.rule.extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclRuleDefinitionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra.acl.rule.extension')) {
            return;
        }

        // Builds an array with service IDs as keys and tag aliases as values
        $definitions = array();

        foreach ($container->findTaggedServiceIds('sonatra.acl.rule_definition') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            // Flip, because we want tag aliases (= definition identifiers) as keys
            $definitions[$alias] = $serviceId;
        }

        $container->getDefinition('sonatra.acl.rule.extension')->replaceArgument(1, $definitions);
    }
}
