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

use Sonatra\Component\Security\Model\SharingInterface;
use Sonatra\Component\Security\Sharing\SharingIdentityConfig;
use Sonatra\Component\Security\Sharing\SharingSubjectConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['sharing']['enabled']) {
            $container->setParameter('sonatra_security.sharing_class', $this->validateSharingClass($config['sharing_class']));
            $loader->load('sharing.xml');
            $this->buildSharingConfigs($container, $config);
            BuilderUtils::loadProvider($loader, $config, 'sharing');
        }

        $this->buildDoctrineSharingFilter($container, $loader, $config);
        $this->buildDoctrineSharingListener($container, $loader, $config);
        $this->buildDoctrineSharingDeleteListener($container, $loader, $config);
    }

    /**
     * Build the sharing configurations.
     *
     * @param ContainerBuilder $container The container
     * @param array            $config    The config
     */
    private function buildSharingConfigs(ContainerBuilder $container, array $config)
    {
        $subjectConfigs = array();
        $identityConfigs = array();

        foreach ($config['sharing']['subjects'] as $type => $subjectConfig) {
            $subjectConfigs[] = $this->buildSharingSubjectConfig($container, $type, $subjectConfig);
        }

        foreach ($config['sharing']['identity_types'] as $type => $identityConfig) {
            $identityConfigs[] = $this->buildSharingIdentityConfig($container, $type, $identityConfig);
        }

        $container->getDefinition('sonatra_security.sharing_manager')->replaceArgument(1, $subjectConfigs);
        $container->getDefinition('sonatra_security.sharing_manager')->replaceArgument(2, $identityConfigs);
    }

    /**
     * Build the doctrine sharing filter.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildDoctrineSharingFilter(ContainerBuilder $container, LoaderInterface $loader,
                                                array $config)
    {
        if ($config['doctrine']['orm']['filters']['sharing']) {
            BuilderUtils::validate($container, 'doctrine.orm.filter.sharing', 'doctrine.orm.entity_manager.class', 'doctrine/orm');

            if (!$config['sharing']['enabled']) {
                throw new InvalidConfigurationException('The "sonatra_security.sharing" config must be enabled');
            }

            $loader->load('orm_filter_sharing.xml');
        }
    }

    /**
     * Build the doctrine sharing listener.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildDoctrineSharingListener(ContainerBuilder $container, LoaderInterface $loader,
                                                  array $config)
    {
        // doctrine orm sharing filter listener for private sharing
        if ($config['doctrine']['orm']['listeners']['private_sharing']) {
            BuilderUtils::validate($container, 'doctrine.orm.listeners.private_sharing', 'doctrine.orm.entity_manager.class', 'doctrine/orm');

            if (!$config['doctrine']['orm']['filters']['sharing']) {
                throw new InvalidConfigurationException('The "sonatra_security.doctrine.orm.filters.sharing" config must be enabled');
            }

            $loader->load('orm_listener_private_sharing.xml');
        }
    }

    /**
     * Build the doctrine sharing delete listener.
     *
     * @param ContainerBuilder $container The container
     * @param LoaderInterface  $loader    The config loader
     * @param array            $config    The config
     */
    private function buildDoctrineSharingDeleteListener(ContainerBuilder $container,
                                                        LoaderInterface $loader,
                                                        array $config)
    {
        // doctrine orm sharing delete listener for private sharing
        if ($config['doctrine']['orm']['listeners']['sharing_delete']) {
            BuilderUtils::validate($container, 'doctrine.orm.listeners.sharing_delete', 'doctrine.orm.entity_manager.class', 'doctrine/orm');

            if (!$config['sharing']['enabled']) {
                throw new InvalidConfigurationException('The "sonatra_security.sharing" config must be enabled');
            }

            $loader->load('orm_listener_sharing_delete.xml');
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
     * Build the sharing subject config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The sharing subject type
     * @param array            $config    The sharing subject config
     *
     * @return Reference
     */
    private function buildSharingSubjectConfig(ContainerBuilder $container, $type, array $config)
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" sharing subject class does not exist';
            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        $def = new Definition(SharingSubjectConfig::class, array(
            $type,
            $config['visibility'],
        ));
        $def->setPublic(false);

        $id = 'sonatra_security.sharing_subject_config.'.strtolower(str_replace('\\', '_', $type));
        $container->setDefinition($id, $def);

        return new Reference($id);
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
}
