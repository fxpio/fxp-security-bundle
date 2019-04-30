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
class OrganizationalContextBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        if ($config['organizational_context']['enabled']) {
            $loader->load('organizational_context.xml');
            $id = 'fxp_security.organizational_context.service_id';
            $container->setParameter($id, $config['organizational_context']['service_id']);
        }
    }
}
