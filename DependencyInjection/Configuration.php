<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of the securitybundle to get the sonatra_security options.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonatra_security');

        $rootNode
            ->children()
                ->scalarNode('user_class')->defaultValue('FOS\UserBundle\Model\UserInterface')->end()
                ->scalarNode('role_class')->defaultValue('Symfony\Component\Security\Core\Role\RoleInterface')->end()
                ->scalarNode('group_class')->defaultValue('FOS\UserBundle\Model\GroupInterface')->end()
            ->end()
            ->append($this->getAclNode())
            ->append($this->getExpressionNode())
            ->append($this->getDoctrineListenerNode())
        ;

        return $treeBuilder;
    }

    /**
     * Get acl node.
     *
     * @return NodeDefinition
     */
    private function getAclNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('acl');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('enabled')->defaultTrue()->end()
                ->scalarNode('enabled_hierarchy')->defaultTrue()->end()
                ->scalarNode('enabled_group_security_identity')->defaultTrue()->end()
                ->scalarNode('doctrine_orm_listener')->defaultTrue()->end()
                ->scalarNode('rule_doctrine_orm_filters')->defaultTrue()->end()
                ->scalarNode('default_rule')->defaultValue('disabled')->end()
                ->arrayNode('rules')
                    ->example(array('Vendor\Entity\Blog' => 'class', 'Vendor\Entity\Post' => 'affirmative'))
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) { return array('default' => strtolower($v)); })
                        ->end()
                        ->children()
                            ->scalarNode('default')->end()
                            ->arrayNode('rules')
                                ->prototype('scalar')
                                    ->beforeNormalization()
                                        ->always(function($v) {return strtolower($v);})
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('default_fields')->end()
                            ->arrayNode('fields')
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function($v) { return array('default' => strtolower($v)); })
                                    ->end()
                                    ->children()
                                        ->scalarNode('default')->end()
                                        ->arrayNode('rules')
                                            ->prototype('scalar')
                                                ->beforeNormalization()
                                                    ->always(function($v) {return strtolower($v);})
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get expression node.
     *
     * @return NodeDefinition
     */
    private function getExpressionNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('expression');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('replace_has_permission')->defaultTrue()->end()
                ->scalarNode('add_has_field_permission')->defaultTrue()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get doctrine listener node.
     *
     * @return NodeDefinition
     */
    private function getDoctrineListenerNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('doctrine_listener');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('enabled')->defaultTrue()->end()
            ->end()
        ;

        return $node;
    }
}
