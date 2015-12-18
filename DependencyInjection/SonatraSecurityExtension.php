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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Sonatra\Bundle\SecurityBundle\Exception\RuntimeException;

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

        // cache dir
        $cacheDir = $container->getParameterBag()->resolveValue($config['cache_dir']);

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }
        }

        // host role
        if ($config['host_role']['enabled']) {
            $loader->load('host_role.xml');
        }

        // role hierarchy
        if ($config['role_hierarchy']['enabled']) {
            $loader->load('role_hierarchy.xml');

            // role hierarchy cache dir
            if (!is_dir($cacheDir.'/role_hierarchy')) {
                if (false === @mkdir($cacheDir.'/role_hierarchy', 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir.'/expressions'));
                }
            }

            $container->setParameter('sonatra_security.cache_dir', $cacheDir);

            // doctrine orm listener role hierarchy
            if ($config['doctrine']['orm']['listener']['role_hierarchy']) {
                $loader->load('orm_listener_role_hierarchy.xml');
            }
        }

        // expression
        if ($config['expression']['has_permission']) {
            $loader->load('expression_has_permission.xml');
        }

        if ($config['expression']['has_field_permission']) {
            $loader->load('expression_has_field_permission.xml');
        }

        if ($config['expression']['has_org_role']) {
            $loader->load('expression_has_org_role.xml');
        }

        // acl
        if ($config['acl']['enabled']
                && $container->hasParameter('security.acl.dbal.class_table_name')
                && $container->hasParameter('security.acl.dbal.entry_table_name')
                && $container->hasParameter('security.acl.dbal.oid_table_name')
                && $container->hasParameter('security.acl.dbal.oid_ancestors_table_name')
                && $container->hasParameter('security.acl.dbal.sid_table_name')) {
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

            $container->setParameter('sonatra_security.acl_default_rule', $config['acl']['default_rule']);
            $container->setParameter('sonatra_security.acl_disabled_rule', $config['acl']['disabled_rule']);
            $container->setParameter('sonatra_security.acl_rules', $config['acl']['rules']);

            // doctrine orm listener acl filter/restaure fields value
            if ($config['doctrine']['orm']['listener']['acl_filter_fields']) {
                $loader->load('orm_listener_acl_filter_fields.xml');
            }

            // doctrine orm rule filters
            if ($config['doctrine']['orm']['filter']['rule_filters']) {
                $loader->load('orm_rule_filter.xml');
            }

            // doctrine orm object filter voters
            if ($config['doctrine']['orm']['object_filter_voter']) {
                $loader->load('orm_object_filter_voter.xml');
            }
        }

        if ($config['organizational_context']['enabled']) {
            $loader->load('organizational_context.xml');
        }
    }
}
