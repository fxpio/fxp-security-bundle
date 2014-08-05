<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Complete YAML Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class YamlCompleteConfigurationTest extends CompleteConfigurationTest
{
    /**
     * {@inheritdoc}
     */
    protected function loadFromFile(ContainerBuilder $container, $file)
    {
        $loadXml = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/yml'));
        $loadXml->load($file.'.yml');
    }

    protected function loadSymfonyFromFile(ContainerBuilder $container, $file)
    {
        $ref = new \ReflectionClass('Symfony\Bundle\SecurityBundle\Tests\DependencyInjection\CompleteConfigurationTest');
        $loadXml = new YamlFileLoader($container, new FileLocator(dirname($ref->getFileName()).'/Fixtures/yml'));
        $loadXml->load($file.'.yml');
    }
}
