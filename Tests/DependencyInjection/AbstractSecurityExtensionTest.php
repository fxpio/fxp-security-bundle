<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\DependencyInjection;

use Fxp\Bundle\SecurityBundle\DependencyInjection\FxpSecurityExtension;
use Fxp\Bundle\SecurityBundle\FxpSecurityBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Base for security extension tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSecurityExtensionTest extends TestCase
{
    /**
     * Create container.
     *
     * @param array $configs    The configs
     * @param array $parameters The container parameters
     * @param array $services   The service definitions
     *
     * @return ContainerBuilder
     */
    protected function createContainer(array $configs = array(), array $parameters = array(), array $services = array())
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles' => array(
                'FrameworkBundle' => FrameworkBundle::class,
                'SecurityBundle' => SecurityBundle::class,
                'FxpSecurityBundle' => FxpSecurityBundle::class,
            ),
            'kernel.bundles_metadata' => array(),
            'kernel.cache_dir' => sys_get_temp_dir().'/fxp_security_bundle',
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => sys_get_temp_dir().'/fxp_security_bundle',
            'kernel.project_dir' => sys_get_temp_dir().'/fxp_security_bundle',
            'kernel.charset' => 'UTF-8',
        )));

        $container->setParameter('doctrine.default_entity_manager', 'test');
        $container->setDefinition('doctrine.orm.test_metadata_driver', new Definition(\stdClass::class));

        $sfExt = new FrameworkExtension();
        $sfSecurityExt = new SecurityExtension();
        $extension = new FxpSecurityExtension();

        $container->registerExtension($sfExt);
        $container->registerExtension($sfSecurityExt);
        $container->registerExtension($extension);

        foreach ($parameters as $name => $value) {
            $container->setParameter($name, $value);
        }

        foreach ($services as $id => $definition) {
            $container->setDefinition($id, $definition);
        }

        $sfExt->load(array(array('form' => true)), $container);
        $extension->load($configs, $container);

        $bundle = new FxpSecurityBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
