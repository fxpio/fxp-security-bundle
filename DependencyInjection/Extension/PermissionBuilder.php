<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Extension;

use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        $loader->load('permission.xml');
        $container->getDefinition('fxp_security.permission_factory')
            ->replaceArgument(2, $config['default_permissions'])
        ;

        $configs = [];

        foreach ($config['permissions'] as $type => $permConfig) {
            if ($permConfig['enabled']) {
                $configs[] = $this->buildPermissionConfig($container, $type, $permConfig);
            }
        }

        $container->getDefinition('fxp_security.permission_loader.configuration')->replaceArgument(0, $configs);
        BuilderUtils::loadProvider($loader, $config, 'permission');
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
    private function buildPermissionConfig(ContainerBuilder $container, string $type, array $config): Reference
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" permission class does not exist';

            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        return $this->createConfigDefinition($container, PermissionConfig::class, $type, [
            $type,
            $config['operations'],
            $config['mapping_permissions'],
            $this->buildPermissionConfigFields($container, $type, $config),
            $config['master'],
            $config['master_mapping_permissions'],
            $config['build_fields'],
            $config['build_default_fields'],
        ]);
    }

    /**
     * Build the fields of permission config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The type of permission
     * @param array            $config    The config of permissions
     *
     * @throws
     *
     * @return string[]
     */
    private function buildPermissionConfigFields(ContainerBuilder $container, string $type, array $config): array
    {
        $fields = [];
        $ref = new \ReflectionClass($type);

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!$this->isValidField($ref, $field)) {
                $msg = 'The permission field "%s" does not exist in "%s" class';

                throw new InvalidConfigurationException(sprintf($msg, $field, $type));
            }

            $fields[] = $this->createConfigDefinition($container, PermissionFieldConfig::class, $type, [
                $field,
                $fieldConfig['operations'],
                $fieldConfig['mapping_permissions'],
                $fieldConfig['editable'] ?? null,
            ], $field);
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
     * @param null|string      $field     The field of permission
     *
     * @return Reference
     */
    private function createConfigDefinition(ContainerBuilder $container, string $class, string $type, array $arguments, ?string $field = null): Reference
    {
        $def = new Definition($class, $arguments);
        $def->setPublic(false);

        $id = 'fxp_security.permission_config.'.strtolower(str_replace('\\', '_', $type));

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
    private function buildDoctrineOrmChecker(ContainerBuilder $container, LoaderInterface $loader, array $config): void
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
    private function isValidField(\ReflectionClass $reflectionClass, string $field): bool
    {
        $getField = 'get'.ucfirst($field);
        $hasField = 'has'.ucfirst($field);
        $isField = 'is'.ucfirst($field);

        return $reflectionClass->hasProperty($field) || $reflectionClass->hasMethod($field) || $reflectionClass->hasMethod($getField) || $reflectionClass->hasMethod($hasField) || $reflectionClass->hasMethod($isField);
    }
}
