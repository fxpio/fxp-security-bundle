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

use Sonatra\Component\Security\SharingTypes;
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
                ->scalarNode('role_class')->defaultValue('Sonatra\Component\Security\Model\RoleInterface')->isRequired()->end()
                ->scalarNode('permission_class')->defaultValue('Sonatra\Component\Security\Model\PermissionInterface')->isRequired()->end()
                ->scalarNode('sharing_class')->defaultValue('Sonatra\Component\Security\Model\SharingInterface')->end()
            ->end()
            ->append($this->getHostRoleNode())
            ->append($this->getRoleHierarchyNode())
            ->append($this->getSecurityVoterNode())
            ->append($this->getObjectFilterNode())
            ->append($this->getOrganizationalContextNode())
            ->append($this->getExpressionLanguageNode())
            ->append($this->getAnnotationNode())
            ->append($this->getPermissionNode())
            ->append($this->getSharingNode())
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
        $node = NodeUtils::createArrayNode('host_role');

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
        $node = NodeUtils::createArrayNode('role_hierarchy');

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
        $node = NodeUtils::createArrayNode('security_voter');

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
        $node = NodeUtils::createArrayNode('object_filter');

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
        $node = NodeUtils::createArrayNode('organizational_context');

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
        $node = NodeUtils::createArrayNode('expression');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('override_voter')->defaultFalse()->end()
                ->arrayNode('functions')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('is_basic_auth')->defaultFalse()->end()
                    ->scalarNode('has_org_role')->defaultFalse()->end()
                    ->scalarNode('is_granted')->defaultFalse()->end()
                ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get annotation node.
     *
     * @return NodeDefinition
     */
    private function getAnnotationNode()
    {
        $node = NodeUtils::createArrayNode('annotations');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('security')->defaultFalse()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get permission node.
     *
     * @return NodeDefinition
     */
    private function getPermissionNode()
    {
        $node = NodeUtils::createArrayNode('permissions');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('permission', false)
            ->normalizeKeys(false)

            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->canBeDisabled()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($v) {
                        return array('sharing' => $v);
                    })
                ->end()
                ->children()
                    ->scalarNode('sharing')->defaultValue(SharingTypes::TYPE_NONE)->end()
                    ->scalarNode('master')->defaultNull()->end()
                    ->booleanNode('build_fields')->defaultTrue()->end()
                    ->arrayNode('fields')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get sharing node.
     *
     * @return NodeDefinition
     */
    private function getSharingNode()
    {
        $node = NodeUtils::createArrayNode('sharing');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->arrayNode('identity_types')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('permission', false)
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return array('alias' => $v);
                            })
                        ->end()
                        ->children()
                            ->scalarNode('alias')->defaultNull()->end()
                            ->booleanNode('roleable')->defaultFalse()->end()
                            ->booleanNode('permissible')->defaultFalse()->end()
                        ->end()
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
        $node = NodeUtils::createArrayNode('doctrine');

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
