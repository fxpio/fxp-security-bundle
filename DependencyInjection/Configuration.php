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

use Fxp\Component\Security\Model\PermissionInterface;
use Fxp\Component\Security\Model\SharingInterface;
use Fxp\Component\Security\SharingVisibilities;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of the securitybundle to get the fxp_security options.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fxp_security');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $supportedDrivers = ['orm', 'custom'];

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
            ->end()
            ->append($this->getHostRoleNode())
            ->append($this->getAnonymousRoleNode())
            ->append($this->getRoleHierarchyNode())
            ->append($this->getSecurityVoterNode())
            ->append($this->getObjectFilterNode())
            ->append($this->getOrganizationalContextNode())
            ->append($this->getExpressionLanguageNode())
            ->append($this->getAnnotationNode())
            ->append($this->getFieldConfigPermissionNode())
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
    private function getHostRoleNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('host_role')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
        ;
    }

    /**
     * Get anonymous role node.
     *
     * @return NodeDefinition
     */
    private function getAnonymousRoleNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('anonymous_role')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
        ;
    }

    /**
     * Get role hierarchy node.
     *
     * @return NodeDefinition
     */
    private function getRoleHierarchyNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('role_hierarchy')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
            ->scalarNode('cache')->defaultNull()->info('The service id of cache')->end()
            ->end()
        ;
    }

    /**
     * Get security voter node.
     *
     * @return NodeDefinition
     */
    private function getSecurityVoterNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('security_voter')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('allow_not_managed_subject')->defaultTrue()->end()
            ->scalarNode('role')->defaultFalse()->end()
            ->scalarNode('group')->defaultFalse()->end()
            ->end()
        ;
    }

    /**
     * Get object filter node.
     *
     * @return NodeDefinition
     */
    private function getObjectFilterNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('object_filter')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
            ->arrayNode('excluded_classes')
            ->prototype('scalar')->end()
            ->defaultValue([
                PermissionInterface::class,
                SharingInterface::class,
            ])
            ->end()
            ->end()
        ;
    }

    /**
     * Get organizational context node.
     *
     * @return NodeDefinition
     */
    private function getOrganizationalContextNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('organizational_context')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
            ->scalarNode('service_id')->defaultNull()->end()
            ->end()
        ;
    }

    /**
     * Get expression language node.
     *
     * @return NodeDefinition
     */
    private function getExpressionLanguageNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('expression')
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
    }

    /**
     * Get annotation node.
     *
     * @return NodeDefinition
     */
    private function getAnnotationNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('annotations')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('include_paths')
            ->defaultValue(['%kernel.project_dir%/src'])
            ->scalarPrototype()->end()
            ->end()
            ->arrayNode('exclude_paths')
            ->scalarPrototype()
            ->end()
            ->end()
            ->booleanNode('permissions')->defaultTrue()->end()
            ->booleanNode('sharing')->defaultTrue()->end()
            ->end()
        ;
    }

    /**
     * Get permission node.
     *
     * @return NodeDefinition
     */
    private function getFieldConfigPermissionNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('default_permissions')
            ->addDefaultsIfNotSet()
            ->append($this->getPermissionFieldsNode())
            ->children()
            ->arrayNode('master_mapping_permissions')
            ->useAttributeAsKey('master_mapping_permission', false)
            ->normalizeKeys(false)
            ->prototype('scalar')->end()
            ->end()
            ->end()
        ;
    }

    /**
     * Get permission node.
     *
     * @return NodeDefinition
     */
    private function getPermissionNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('permissions')
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('permission', false)
            ->normalizeKeys(false)

            ->prototype('array')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
            ->scalarNode('master')->defaultNull()->end()
            ->arrayNode('master_mapping_permissions')
            ->useAttributeAsKey('master_mapping_permission', false)
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
            ->booleanNode('build_default_fields')->defaultTrue()->end()
            ->append($this->getPermissionFieldsNode())
            ->end()
            ->end()
        ;
    }

    /**
     * Get permission fields node.
     *
     * @return NodeDefinition
     */
    private function getPermissionFieldsNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('fields')
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('field', false)
            ->normalizeKeys(false)
            ->prototype('array')
            ->addDefaultsIfNotSet()
            ->beforeNormalization()
            ->ifTrue(static function ($v) {
                return \is_array($v) && isset($v[0]);
            })
            ->then(static function ($v) {
                return ['operations' => $v];
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
            ->booleanNode('editable')->defaultNull()->end()
            ->end()
            ->end()
        ;
    }

    /**
     * Get sharing node.
     *
     * @return NodeDefinition
     */
    private function getSharingNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('sharing')
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
            ->then(static function ($v) {
                return ['visibility' => $v];
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
            ->then(static function ($v) {
                return ['alias' => $v];
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
    }

    /**
     * Get doctrine node.
     *
     * @return NodeDefinition
     */
    private function getDoctrineNode(): NodeDefinition
    {
        return NodeUtils::createArrayNode('doctrine')
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
            ->scalarNode('private_sharing')->defaultfalse()->info('Require to enable the "fxp_security.doctrine.orm.filters.sharing" option')->end()
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
    }
}
