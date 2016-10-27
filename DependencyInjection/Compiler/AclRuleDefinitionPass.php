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
 * Adds all services with the tags "sonatra_security.acl.rule_definition" as arguments
 * of the "sonatra_security.acl.rule_extension" service.
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
        if (!$container->hasDefinition('sonatra_security.acl.rule_extension')) {
            return;
        }

        $this->injectRuleServices($container, 'sonatra_security.acl.rule_definition', 0);
        $this->injectRuleServices($container, 'sonatra_security.acl.rule_filter_definition', 1);
    }

    /**
     * Builds an array with service IDs as keys and tag aliases as values.
     *
     * @param ContainerBuilder $container   The container builder
     * @param string           $tag         The service tag name
     * @param int              $argPosition The position of argument replacement
     */
    private function injectRuleServices(ContainerBuilder $container, $tag, $argPosition)
    {
        $definitions = array();

        foreach ($container->findTaggedServiceIds($tag) as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            // flip, because we want tag aliases (= definition identifiers) as keys
            $definitions[$alias] = $serviceId;
        }

        $container->getDefinition('sonatra_security.acl.rule_extension')->replaceArgument($argPosition, $definitions);
    }
}
