<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension;

use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Permission\PermissionFieldConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        $loader->load('permission.xml');
        $configs = array();

        foreach ($config['permissions'] as $type => $permConfig) {
            if ($permConfig['enabled']) {
                $configs[] = $this->buildPermissionConfig($container, $type, $permConfig);
            }
        }

        $container->getDefinition('sonatra_security.permission_manager')->replaceArgument(4, $configs);
        BuilderUtils::loadProvider($loader, $config, 'permission');
        $this->buildDoctrineOrmProvider($container, $config);
        $this->buildDoctrineOrmChecker($container, $loader, $config);
    }

    /**
     * Build the permission config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The type of permission
     * @param array            $config    The config of permissions
     *
     * @return Reference
     */
    private function buildPermissionConfig(ContainerBuilder $container, $type, array $config)
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" permission class does not exist';
            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        return $this->createConfigDefinition($container, PermissionConfig::class, $type, array(
            $type,
            $config['operations'],
            $config['mapping_permissions'],
            $this->buildPermissionConfigFields($container, $type, $config),
            $config['master'],
            $config['master_mapping_permissions'],
        ));
    }

    /**
     * Build the fields of permission config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The type of permission
     * @param array            $config    The config of permissions
     *
     * @return string[]
     */
    private function buildPermissionConfigFields(ContainerBuilder $container, $type, array $config)
    {
        $fields = array();
        $ref = new \ReflectionClass($type);

        if ($config['build_fields'] && 0 === count($config['fields'])) {
            foreach ($ref->getProperties() as $property) {
                $config['fields'][$property->getName()] = array(
                    'enabled' => true,
                    'operations' => array(),
                    'mapping_permissions' => array(),
                );
            }
        }

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!$ref->hasProperty($field)) {
                $msg = 'The permission field "%s" does not exist in "%s" class';

                throw new InvalidConfigurationException(sprintf($msg, $field, $type));
            }

            $fields[] = $this->createConfigDefinition($container, PermissionFieldConfig::class, $type, array(
                $field,
                $fieldConfig['operations'],
                $fieldConfig['mapping_permissions'],
            ), $field);
        }

        return $fields;
    }

    /**
     * Create the permission configuration service and get the service id reference.
     *
     * @param ContainerBuilder $container The container
     * @param string           $class     The config class
     * @param string           $type      The type of permission
     * @param array            $arguments The config class arguments
     * @param string|null      $field     The field of permission
     *
     * @return Reference
     */
    private function createConfigDefinition(ContainerBuilder $container, $class, $type, array $arguments, $field = null)
    {
        $def = new Definition($class, $arguments);
        $def->setPublic(false);

        $id = 'sonatra_security.permission_config.'.strtolower(str_replace('\\', '_', $type));

        if (null !== $field) {
            $id .= '.fields.'.$field;
        }

        $container->setDefinition($id, $def);

        return new Reference($id);
    }

    /**
     * Build the config of merge organizational roles with doctrine orm permission listener.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The config
     */
    private function buildDoctrineOrmProvider(ContainerBuilder $container, array $config)
    {
        $def = $container->getDefinition('sonatra_security.permission_provider');
        $def->replaceArgument(3, $config['doctrine']['orm']['providers']['merge_organizational_roles']);
    }

    /**
     * Build the config of doctrine orm permission checker listener.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildDoctrineOrmChecker(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['doctrine']['orm']['listeners']['permission_checker']) {
            BuilderUtils::validate($container, 'doctrine.orm.listeners.permission_checker', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
            $loader->load('orm_listener_permission_checker.xml');
        }
    }
}
