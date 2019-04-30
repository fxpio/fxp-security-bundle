<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of the securitybundle to get the fxp_security options.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AccessControlConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('security');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->ignoreExtraKeys()
            ->fixXmlConfig('rule', 'access_control')
            ->append($this->getAccessControlNode())
        ;

        return $treeBuilder;
    }

    /**
     * Get access control node.
     *
     * @return NodeDefinition
     */
    private function getAccessControlNode()
    {
        $node = NodeUtils::createArrayNode('access_control');

        $node
            ->cannotBeOverwritten()
            ->prototype('array')
            ->fixXmlConfig('ip')
            ->fixXmlConfig('method')
            ->children()
            ->scalarNode('requires_channel')->defaultNull()->end()
            ->scalarNode('path')
            ->defaultNull()
            ->info('use the urldecoded format')
            ->example('^/path to resource/')
            ->end()
            ->scalarNode('host')->defaultNull()->end()
            ->arrayNode('ips')
            ->beforeNormalization()->ifString()->then(function ($v) {
                return [$v];
            })->end()
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('methods')
            ->beforeNormalization()->ifString()->then(function ($v) {
                return preg_split('/\s*,\s*/', $v);
            })->end()
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode('allow_if')->defaultNull()->end()
            ->end()
            ->fixXmlConfig('role')
            ->children()
            ->arrayNode('roles')
            ->beforeNormalization()->ifString()->then(function ($v) {
                return preg_split('/\s*,\s*/', $v);
            })->end()
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
        ;

        return $node;
    }
}
