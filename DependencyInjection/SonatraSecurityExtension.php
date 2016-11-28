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

use Sonatra\Component\Security\Model\SharingInterface;
use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Sharing\SharingIdentityConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
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

        $this->buildModel($container, $config);
        $this->buildSecurityIdentity($loader);
        $this->buildPermission($container, $loader, $config);
        $this->buildObjectFilter($container, $loader, $config);
        $this->buildHostRole($container, $loader, $config);
        $this->buildRoleHierarchy($container, $loader, $config);
        $this->buildSecurityVoter($loader, $config);
        $this->buildOrganizationalContext($container, $loader, $config);
        $this->buildExpressionLanguage($loader, $config);
        $this->buildAnnotation($container, $loader, $config);
        $this->buildSharing($container, $loader, $config);
    }

    /**
     * Build the models.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The config
     */
    private function buildModel(ContainerBuilder $container, array $config)
    {
        if ('custom' !== $config['db_driver']) {
            $container->setParameter($this->getAlias().'.backend_type_'.$config['db_driver'], true);
        }

        $container->setParameter('sonatra_security.role_class', $config['role_class']);
        $container->setParameter('sonatra_security.permission_class', $config['permission_class']);
    }

    /**
     * Build the security identity manager.
     *
     * @param LoaderInterface $loader The config loader
     */
    private function buildSecurityIdentity(LoaderInterface $loader)
    {
        $loader->load('security_identity.xml');
    }

    /**
     * Build the permission.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildPermission(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        $loader->load('permission.xml');
        $configs = array();

        foreach ($config['permissions'] as $type => $permConfig) {
            if ($permConfig['enabled']) {
                $configs[] = $this->buildPermissionConfig($container, $type, $permConfig);
            }
        }

        $container->getDefinition('sonatra_security.permission_manager')->replaceArgument(3, $configs);

        if ('custom' !== $config['db_driver']) {
            $loader->load($config['db_driver'].'_permission_provider.xml');
        }
    }

    /**
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

        $def = new Definition(PermissionConfig::class, array(
            $type,
            $this->buildPermissionConfigFields($type, $config),
            $config['sharing'],
            $config['master'],
        ));
        $def->setPublic(false);

        $id = 'sonatra_security.permission_config.'.strtolower(str_replace('\\', '_', $type));
        $container->setDefinition($id, $def);

        return new Reference($id);
    }

    /**
     * Build the fields of permission config.
     *
     * @param string $type   The type of permission
     * @param array  $config The config of permissions
     *
     * @return string[]
     */
    private function buildPermissionConfigFields($type, array $config)
    {
        $fields = array();
        $ref = new \ReflectionClass($type);

        if ($config['build_fields'] && 0 === count($config['fields'])) {
            foreach ($ref->getProperties() as $property) {
                $fields[] = $property->getName();
            }
        } else {
            foreach ($config['fields'] as $field) {
                if (!$ref->hasProperty($field)) {
                    $msg = 'The permission field "%s" does not exist in "%s" class';
                    throw new InvalidConfigurationException(sprintf($msg, $field, $type));
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Build the object filter.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildObjectFilter(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['object_filter']['enabled']) {
            $loader->load('object_filter.xml');

            // doctrine orm object filter voters
            if ($config['doctrine']['orm']['object_filter_voter']) {
                $this->validate($container, 'doctrine.orm.object_filter_voter', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_object_filter_voter.xml');
            }

            // doctrine orm object filter listener
            if ($config['doctrine']['orm']['listeners']['object_filter']) {
                $this->validate($container, 'doctrine.orm.listeners.object_filter', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_listener_object_filter.xml');
            }
        }
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
     * Build the security voter.
     *
     * @param LoaderInterface $loader The config loader
     * @param array           $config The config
     */
    private function buildSecurityVoter(LoaderInterface $loader, array $config)
    {
        if ($config['security_voter']['role_security_identity']) {
            $loader->load('security_voter_role_security_identity.xml');
        }

        if ($config['security_voter']['groupable']) {
            $loader->load('security_voter_groupable.xml');
        }
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

            // doctrine orm role hierarchy listener
            if ($config['doctrine']['orm']['listeners']['role_hierarchy']) {
                $this->validate($container, 'doctrine.orm.listeners.role_hierarchy', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_listener_role_hierarchy.xml');
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
            $loader->load('organizational_role.xml');
            $id = 'sonatra_security.organizational_context.service_id';
            $container->setParameter($id, $config['organizational_context']['service_id']);
        }
    }

    /**
     * Build the expression language.
     *
     * @param LoaderInterface $loader The config loader
     * @param array           $config The config
     */
    private function buildExpressionLanguage(LoaderInterface $loader, array $config)
    {
        $loader->load('expression_variable_storage.xml');

        if ($config['expression']['override_voter']) {
            $loader->load('expression_voter.xml');
        }

        foreach ($config['expression']['functions'] as $function => $enabled) {
            if ($enabled) {
                $loader->load(sprintf('expression_function_%s.xml', $function));
            }
        }
    }

    /**
     * Build the annotation.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildAnnotation(ContainerBuilder $container, LoaderInterface $loader,
                                     array $config)
    {
        if ($config['annotations']['security']) {
            $this->validate($container, 'annotations.security', 'sensio_framework_extra.view.guesser.class', 'sensio/framework-extra-bundle');
            $loader->load('annotation_security.xml');

            $this->addClassesToCompile(array(
                'Sonatra\\Bundle\\SecurityBundle\\Listener\\SecurityAnnotationSubscriber',
            ));
        }
    }

    /**
     * Build the sharing.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildSharing(ContainerBuilder $container, LoaderInterface $loader,
                                                array $config)
    {
        if ($config['sharing']['enabled']) {
            $container->setParameter('sonatra_security.sharing_class', $this->validateSharingClass($config['sharing_class']));
            $loader->load('sharing.xml');
            $configs = array();

            foreach ($config['sharing']['identity_types'] as $type => $identityConfig) {
                $configs[] = $this->buildSharingIdentityConfig($container, $type, $identityConfig);
            }

            $container->getDefinition('sonatra_security.sharing_manager')->replaceArgument(0, $configs);
        }

        if ($config['doctrine']['orm']['filters']['sharing']) {
            $this->validate($container, 'doctrine.orm.filter.sharing', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
            $loader->load('orm_filter_sharing.xml');
        }
    }

    /**
     * Validate the sharing class.
     *
     * @param string $class The class name
     *
     * @return string
     */
    private function validateSharingClass($class)
    {
        if (SharingInterface::class === $class || !class_exists($class)) {
            $msg = 'The "sonatra_security.sharing_class" config must be configured with a valid class';
            throw new InvalidConfigurationException($msg);
        }

        return $class;
    }

    /**
     * Build the sharing identity config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The sharing identity type
     * @param array            $config    The sharing identity config
     *
     * @return Reference
     */
    private function buildSharingIdentityConfig(ContainerBuilder $container, $type, array $config)
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" sharing identity class does not exist';
            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        $def = new Definition(SharingIdentityConfig::class, array(
            $type,
            $config['alias'],
            $config['roleable'],
            $config['permissible'],
        ));
        $def->setPublic(false);

        $id = 'sonatra_security.sharing_identity_config.'.strtolower(str_replace('\\', '_', $type));
        $container->setDefinition($id, $def);

        return new Reference($id);
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
