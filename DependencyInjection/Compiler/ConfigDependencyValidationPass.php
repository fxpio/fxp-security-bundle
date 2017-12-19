<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Validate the service dependencies of the configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigDependencyValidationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('sonatra_security.missing_services')) {
            return;
        }

        $missingServices = $container->getParameter('sonatra_security.missing_services');

        foreach ($missingServices as $config => $serviceInfo) {
            list($service, $package) = $serviceInfo;

            if (!$container->hasDefinition($service) && !$container->hasAlias($service)) {
                $msg = 'The "sonatra_security.%s" config require the "%s" package';

                throw new InvalidConfigurationException(sprintf($msg, $config, $package));
            }
        }
    }
}
