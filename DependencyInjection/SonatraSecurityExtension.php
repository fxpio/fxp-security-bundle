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
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
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
        $loader = new Loader\YamlFileLoader($container,new FileLocator(__DIR__ . '/../Resources/config'));

        // entity classes
        $container->setParameter('sonatra_security.user_class', $config['user_class']);
        $container->setParameter('sonatra_security.role_class', $config['role_class']);
        $container->setParameter('sonatra_security.group_class', $config['group_class']);

        // cache dir
        $cacheDir = $container->getParameterBag()->resolveValue($config['cache_dir']);

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }
        }

        // host role
        if ($config['host_role']['enabled']) {
            $loader->load('host_role.yml');
        }

        // role hierarchy
        if ($config['role_hierarchy']['enabled']) {
            $loader->load('role_hierarchy.yml');

            // role hierarchy cache dir
            if (!is_dir($cacheDir.'/role_hierarchy')) {
                if (false === @mkdir($cacheDir.'/role_hierarchy', 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir.'/expressions'));
                }
            }

            $container->setParameter('sonatra_security.role_hierarchy.cache_dir', $cacheDir.'/role_hierarchy');

            // doctrine orm listener role hierarchy
            if ($config['doctrine']['orm']['listener']['role_hierarchy']) {
                $loader->load('orm_listener_role_hierarchy.yml');
            }
        }

        // expression
        if ($config['expression']['has_permission']) {
            $loader->load('expression_has_permission.yml');
        }

        if ($config['expression']['has_field_permission']) {
            $loader->load('expression_has_field_permission.yml');
        }

        // acl
        if ($config['acl']['enabled']
                && $container->hasParameter('security.acl.dbal.class_table_name')
                && $container->hasParameter('security.acl.dbal.entry_table_name')
                && $container->hasParameter('security.acl.dbal.oid_table_name')
                && $container->hasParameter('security.acl.dbal.oid_ancestors_table_name')
                && $container->hasParameter('security.acl.dbal.sid_table_name')) {
            if ($config['acl']['security_identity']) {
                $loader->load('group_security_identity_strategy.yml');
            }

            $loader->load('acl.yml');

            $container->setParameter('sonatra_security.acl_default_rule', $config['acl']['default_rule']);
            $container->setParameter('sonatra_security.acl_disabled_rule', $config['acl']['disabled_rule']);
            $container->setParameter('sonatra_security.acl_rules', $config['acl']['rules']);

            // doctrine orm listener acl filter/restaure fields value
            if ($config['doctrine']['orm']['listener']['acl_filter_fields']) {
                $loader->load('orm_listener_acl_filter_fields.yml');
            }

            // doctrine orm rule filters
            if ($config['doctrine']['orm']['filter']['rule_filters']) {
                $loader->load('orm_rule_filter.yml');
            }

            // doctrine orm object filter voters
            if ($config['doctrine']['orm']['object_filter_voter']) {
                $loader->load('orm_object_filter_voter.yml');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configValidate(ContainerBuilder $container)
    {
        foreach (array_keys($this->entityManagers) as $name) {
            if (!$container->hasDefinition(sprintf('doctrine.dbal.%s_connection', $name))) {
                throw new InvalidArgumentException(sprintf('Invalid %s config: DBAL connection "%s" not found', $this->getAlias(), $name));
            }
        }

        foreach (array_keys($this->documentManagers) as $name) {
            if (!$container->hasDefinition(sprintf('doctrine.odm.mongodb.%s_document_manager', $name))) {
                throw new InvalidArgumentException(sprintf('Invalid %s config: document manager "%s" not found', $this->getAlias(), $name));
            }
        }
    }

    /**
     * This function analyses the classname to change / in \\ if there are some in the given classname.
     *
     * @param string $className The class name to be converted with antislashes
     */
    private function getNormalizedClassName($className)
    {
        $className = str_replace('/', '\\', $className);

        return $className;
    }
}
