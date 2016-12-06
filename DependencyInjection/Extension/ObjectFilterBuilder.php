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
class ObjectFilterBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['object_filter']['enabled']) {
            $loader->load('object_filter.xml');

            // doctrine orm object filter voters
            if ($config['doctrine']['orm']['object_filter_voter']) {
                BuilderUtils::validate($container, 'doctrine.orm.object_filter_voter', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_voter_object_filter.xml');
            }

            // doctrine orm object filter listener
            if ($config['doctrine']['orm']['listeners']['object_filter']) {
                BuilderUtils::validate($container, 'doctrine.orm.listeners.object_filter', 'doctrine.orm.entity_manager.class', 'doctrine/orm');
                $loader->load('orm_listener_object_filter.xml');
            }
        }
    }
}
