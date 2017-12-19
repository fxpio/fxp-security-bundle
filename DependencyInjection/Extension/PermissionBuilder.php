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
        $defaultPerms = $config['default_permissions'];
        $configs = array();

        foreach ($config['permissions'] as $type => $permConfig) {
            if ($permConfig['enabled']) {
                $configs[] = $this->buildPermissionConfig($container, $type, $permConfig, $defaultPerms);
            }
        }

        $container->getDefinition('sonatra_security.permission_manager')->replaceArgument(4, $configs);
        BuilderUtils::loadProvider($loader, $config, 'permission');
        $this->buildDoctrineOrmChecker($container, $loader, $config);
    }

    /**
     * Build the permission config.
     *
     * @param ContainerBuilder $container    The container
     * @param string           $type         The type of permission
     * @param array            $config       The config of permissions
     * @param array            $defaultPerms The config of default permissions
     *
     * @return Reference
     */
    private function buildPermissionConfig(ContainerBuilder $container, $type, array $config, array $defaultPerms)
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" permission class does not exist';
            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        return $this->createConfigDefinition($container, PermissionConfig::class, $type, array(
            $type,
            $config['operations'],
            $config['mapping_permissions'],
            $this->buildPermissionConfigFields($container, $type, $config, $defaultPerms['fields']),
            $config['master'],
            $this->buildMasterMappingPermissions($config, $defaultPerms['master_mapping_permissions']),
        ));
    }

    /**
     * Build the master mapping permissions.
     *
     * @param array $config         The config of permissions
     * @param array $defaultMapping The config of default master mapping permissions
     *
     * @return array
     */
    private function buildMasterMappingPermissions(array $config, array $defaultMapping)
    {
        $mapping = $config['master_mapping_permissions'];

        if (!empty($defaultMapping) && empty($mapping) && null !== $config['master']) {
            $mapping = $defaultMapping;
        }

        return $mapping;
    }

    /**
     * Build the fields of permission config.
     *
     * @param ContainerBuilder $container    The container
     * @param string           $type         The type of permission
     * @param array            $config       The config of permissions
     * @param array            $defaultPerms The config of default permissions
     *
     * @return string[]
     */
    private function buildPermissionConfigFields(ContainerBuilder $container, $type, array $config, array $defaultPerms)
    {
        $fields = array();
        $ref = new \ReflectionClass($type);
        $config = $this->buildDefaultPermissionConfigFields($ref, $config, $defaultPerms);

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!$this->isValidField($ref, $field)) {
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
     * Build the fields of permission config with all class properties and defaults.
     *
     * @param \ReflectionClass $ref          The reflection class
     * @param array            $config       The config of permissions
     * @param array            $defaultPerms The config of default permissions
     *
     * @return array
     */
    private function buildDefaultPermissionConfigFields(\ReflectionClass $ref, array $config, array $defaultPerms)
    {
        $hasFields = count($config['fields']) > 0;
        $hasDefaults = count($defaultPerms) > 0;
        $buildField = $config['build_fields'] && !$hasFields;
        $buildDefaultField = $config['build_default_fields'] && $hasDefaults;

        if ($buildField || $buildDefaultField) {
            foreach ($ref->getProperties() as $property) {
                $field = $property->getName();

                if ($buildDefaultField && !isset($config['fields'][$field]) && isset($defaultPerms[$field])) {
                    $config['fields'][$field] = $defaultPerms[$field];
                } elseif ($buildField && !$hasFields) {
                    $config['fields'][$field] = array(
                        'enabled' => true,
                        'operations' => array(),
                        'mapping_permissions' => array(),
                    );
                }
            }
        }

        return $config;
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
     * Build the config of doctrine orm permission checker listener.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     *
     * @throws
     */
    private function buildDoctrineOrmChecker(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['doctrine']['orm']['listeners']['permission_checker']) {
            BuilderUtils::validate($container, 'doctrine.orm.listeners.permission_checker', 'doctrine.orm.entity_manager', 'doctrine/orm');
            $loader->load('orm_listener_permission_checker.xml');
        }
    }

    /**
     * Check if the permission field is valid.
     *
     * @param \ReflectionClass $reflectionClass The reflection class
     * @param string           $field           The field name
     *
     * @return bool
     */
    private function isValidField(\ReflectionClass $reflectionClass, $field)
    {
        $getField = 'get'.ucfirst($field);
        $hasField = 'has'.ucfirst($field);
        $isField = 'is'.ucfirst($field);

        return $reflectionClass->hasProperty($field) || $reflectionClass->hasMethod($field) || $reflectionClass->hasMethod($getField) || $reflectionClass->hasMethod($hasField) || $reflectionClass->hasMethod($isField);
    }
}
