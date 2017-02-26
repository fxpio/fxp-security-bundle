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

use Sonatra\Component\Security\SharingVisibilities;
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
            ->append($this->getAnonymousRoleNode())
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
     * Get anonymous role node.
     *
     * @return NodeDefinition
     */
    private function getAnonymousRoleNode()
    {
        $node = NodeUtils::createArrayNode('anonymous_role');

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
                    ->scalarNode('is_granted')->defaultFalse()->end()
                    ->scalarNode('is_organization')->defaultFalse()->end()
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
                ->children()
                    ->scalarNode('master')->defaultNull()->end()
                    ->arrayNode('master_mapping_permissions')
                        ->useAttributeAsKey('master_permission', false)
                        ->normalizeKeys(false)
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('operations')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('mapping_permissions')
                        ->useAttributeAsKey('mapping_permission', false)
                        ->normalizeKeys(false)
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('build_fields')->defaultTrue()->end()
                    ->append($this->getPermissionFieldsNode())
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get permission fields node.
     *
     * @return NodeDefinition
     */
    private function getPermissionFieldsNode()
    {
        $node = NodeUtils::createArrayNode('fields');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('field', false)
            ->normalizeKeys(false)
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()
                    ->ifArray()
                    ->then(function ($v) {
                        return array('operations' => $v);
                    })
                ->end()
                ->canBeDisabled()
                ->children()
                    ->arrayNode('operations')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('mapping_permissions')
                        ->useAttributeAsKey('mapping_permission', false)
                        ->normalizeKeys(false)
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
                ->arrayNode('subjects')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('subject', false)
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return array('visibility' => $v);
                            })
                        ->end()
                        ->children()
                            ->scalarNode('visibility')->defaultValue(SharingVisibilities::TYPE_NONE)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('identity_types')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('identity', false)
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
                                ->scalarNode('permission_checker')->defaultfalse()->end()
                                ->scalarNode('object_filter')->defaultfalse()->end()
                                ->scalarNode('private_sharing')->defaultfalse()->info('Require to enable the "sonatra_security.doctrine.orm.filters.sharing" option')->end()
                                ->scalarNode('sharing_delete')->defaultfalse()->end()
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
