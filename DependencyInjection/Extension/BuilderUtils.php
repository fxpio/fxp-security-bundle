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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BuilderUtils
{
    /**
     * Validate the configuration.
     *
     * @param ContainerBuilder $container The container
     * @param string           $config    The name of config
     * @param string           $service   The required service id
     * @param string           $package   The required package name
     */
    public static function validate(ContainerBuilder $container, $config, $service, $package)
    {
        $missingServices = $container->hasParameter('sonatra_security.missing_services')
            ? $container->getParameter('sonatra_security.missing_services')
            : array();

        $missingServices[$config] = array($service, $package);
        $container->setParameter('sonatra_security.missing_services', $missingServices);
    }

    /**
     * Load the database provider.
     *
     * @param LoaderInterface $loader The config loader
     * @param array           $config The config
     * @param string          $type   The provider type
     *
     * @throws
     */
    public static function loadProvider(LoaderInterface $loader, array $config, $type)
    {
        if ('custom' !== $config['db_driver']) {
            $loader->load($config['db_driver'].'_provider_'.$type.'.xml');
        }
    }
}
