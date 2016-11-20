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

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
        $supportedDrivers = array('orm', 'custom');

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->cannotBeEmpty()
                    ->defaultValue('orm')
                ->end()
                ->scalarNode('role_class')->defaultValue('Sonatra\Component\Security\Model\RoleInterface')->end()
            ->end()
            ->append($this->getHostRoleNode())
            ->append($this->getRoleHierarchyNode())
            ->append($this->getSecurityVoterNode())
            ->append($this->getObjectFilterNode())
            ->append($this->getOrganizationalContextNode())
            ->append($this->getExpressionLanguageNode())
            ->append($this->getDoctrineNode())
        ;

        return $treeBuilder;
    }

    /**
     * Get host role node.
     *
     * @return NodeDefinition
     */
    private function getHostRoleNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('host_role');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
        ;

        return $node;
    }

    /**
     * Get role hierarchy node.
     *
     * @return NodeDefinition
     */
    private function getRoleHierarchyNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('role_hierarchy');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->scalarNode('cache')->defaultNull()->info('The service id of cache')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get security voter node.
     *
     * @return NodeDefinition
     */
    private function getSecurityVoterNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('security_voter');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('role_security_identity')->defaultFalse()->end()
                ->scalarNode('groupable')->defaultFalse()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get object filter node.
     *
     * @return NodeDefinition
     */
    private function getObjectFilterNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('object_filter');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
        ;

        return $node;
    }

    /**
     * Get organizational context node.
     *
     * @return NodeDefinition
     */
    private function getOrganizationalContextNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('organizational_context');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->scalarNode('service_id')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get expression language node.
     *
     * @return NodeDefinition
     */
    private function getExpressionLanguageNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('expression');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('override_voter')->defaultFalse()->end()
                ->arrayNode('functions')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('is_basic_auth')->defaultFalse()->end()
                ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get doctrine node.
     *
     * @return NodeDefinition
     */
    private function getDoctrineNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('doctrine');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('orm')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('listeners')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('role_hierarchy')->defaultFalse()->end()
                                ->scalarNode('object_filter')->defaultfalse()->end()
                            ->end()
                        ->end()
                        ->scalarNode('object_filter_voter')->defaultFalse()->end()
                        ->arrayNode('filters')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('sharing')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
