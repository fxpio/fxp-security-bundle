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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class HostRoleBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        $loader->load('host_role.xml');

        $def = $container->getDefinition('fxp_security.authentication.listener.host_roles');
        $def->addMethodCall('setEnabled', [$config['host_role']['enabled']]);
    }
}
