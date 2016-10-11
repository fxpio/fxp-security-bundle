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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The extension that fulfills the infos for the container from configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $ref = new \ReflectionClass($this);
        $configPath = dirname(dirname($ref->getFileName())).'/Resources/config';
        $loader = new Loader\XmlFileLoader($container, new FileLocator($configPath));

        // entity classes
        $container->setParameter('sonatra_security.user_class', $config['user_class']);
        $container->setParameter('sonatra_security.role_class', $config['role_class']);
        $container->setParameter('sonatra_security.group_class', $config['group_class']);
        $container->setParameter('sonatra_security.organization_class', $config['organization_class']);

        $this->buildHostRole($container, $loader, $config);
        $this->buildRoleHierarchy($container, $loader, $config);
        $this->buildExpression($container, $loader, $config);
        $this->buildAcl($container, $loader, $config);
        $this->buildOrganizationalContext($container, $loader, $config);
    }

    /**
     * Build the host role.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildHostRole(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        $loader->load('host_role.xml');

        $def = $container->getDefinition('sonatra_security.host_role.authentication.listener');
        $def->addMethodCall('setEnabled', array($config['host_role']['enabled']));
    }

    /**
     * Build the role hierarchy.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildRoleHierarchy(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['role_hierarchy']['enabled']) {
            $this->validate($container, 'role_hierarchy', 'doctrine.class', 'doctrine/doctrine-bundle');
            $loader->load('role_hierarchy.xml');

            // role hierarchy cache
            if (null !== ($cacheId = $config['role_hierarchy']['cache'])) {
                $cacheAlias = new Alias($cacheId, false);
                $container->setAlias('sonatra_security.role_hierarchy.cache', $cacheAlias);
            }

            // doctrine orm listener role hierarchy
            if ($config['doctrine']['orm']['listener']['role_hierarchy']) {
                $this->validate($container, 'doctrine.orm.listener.role_hierarchy', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_listener_role_hierarchy.xml');
            }
        }
    }

    /**
     * Build the expression.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildExpression(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['expression']['has_permission']) {
            $this->validate($container, 'expression.has_permission', 'security.expressions.compiler.class', 'jms/security-extra-bundle');
            $loader->load('expression_has_permission.xml');
        }

        if ($config['expression']['has_field_permission']) {
            $this->validate($container, 'expression.has_field_permission', 'security.expressions.compiler.class', 'jms/security-extra-bundle');
            $loader->load('expression_has_field_permission.xml');
        }

        if ($config['expression']['has_role']) {
            $this->validate($container, 'expression.has_role', 'security.expressions.compiler.class', 'jms/security-extra-bundle');
            $loader->load('expression_has_role.xml');
        }

        if ($config['expression']['has_any_role']) {
            $this->validate($container, 'expression.has_any_role', 'security.expressions.compiler.class', 'jms/security-extra-bundle');
            $loader->load('expression_has_any_role.xml');
        }

        if ($config['expression']['has_org_role']) {
            $this->validate($container, 'expression.has_org_role', 'security.expressions.compiler.class', 'jms/security-extra-bundle');
            $loader->load('expression_has_org_role.xml');
        }
    }

    /**
     * Build the ACL.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildAcl(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['acl']['enabled']
                && $container->hasParameter('security.acl.dbal.class_table_name')
                && $container->hasParameter('security.acl.dbal.entry_table_name')
                && $container->hasParameter('security.acl.dbal.oid_table_name')
                && $container->hasParameter('security.acl.dbal.oid_ancestors_table_name')
                && $container->hasParameter('security.acl.dbal.sid_table_name')) {
            $this->validate($container, 'acl', 'doctrine.class', 'doctrine/doctrine-bundle');

            if ($config['acl']['security_identity']['enabled']) {
                $loader->load('security_identity_strategy.xml');
            }

            if ($config['acl']['access_voter']['enabled']) {
                if ($config['acl']['access_voter']['role_security_identity']) {
                    $loader->load('access_voter_role_security_identity.xml');
                }

                if ($config['acl']['access_voter']['groupable']) {
                    $loader->load('access_voter_groupable.xml');
                }
            }

            $loader->load('acl.xml');
            $loader->load('acl_rule.xml');

            $container->setParameter('sonatra_security.acl_default_rule', $config['acl']['default_rule']);
            $container->setParameter('sonatra_security.acl_disabled_rule', $config['acl']['disabled_rule']);
            $container->setParameter('sonatra_security.acl_rules', $config['acl']['rules']);

            // doctrine orm listener acl filter/restaure fields value
            if ($config['doctrine']['orm']['listener']['acl_filter_fields']) {
                $this->validate($container, 'doctrine.orm.listener.acl_filter_fields', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_listener_acl_filter_fields.xml');
            }

            // doctrine orm rule filters
            if ($config['doctrine']['orm']['filter']['rule_filters']) {
                $this->validate($container, 'doctrine.orm.filter.rule_filters', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_rule_filter.xml');
            }

            // doctrine orm object filter voters
            if ($config['doctrine']['orm']['object_filter_voter']) {
                $this->validate($container, 'doctrine.orm.object_filter_voter', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_object_filter_voter.xml');
            }
        }
    }

    /**
     * Build the organizational context.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildOrganizationalContext(ContainerBuilder $container, LoaderInterface $loader,
                                                array $config)
    {
        if ($config['organizational_context']['enabled']) {
            $loader->load('organizational_context.xml');
            $id = 'sonatra_security.organizational_context.service_id';
            $container->setParameter($id, $config['organizational_context']['service_id']);
        }
    }

    /**
     * Validate the configuration.
     *
     * @param ContainerBuilder $container The container
     * @param string           $config    The name of config
     * @param string           $parameter The required parameter
     * @param string           $package   The required package name
     */
    private function validate(ContainerBuilder $container, $config, $parameter, $package)
    {
        if (!$container->hasParameter($parameter)) {
            $msg = 'The "sonatra_security.%s" config require the "%s" package';
            throw new InvalidConfigurationException(sprintf($msg, $config, $package));
        }
    }
}
