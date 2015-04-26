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

use Sonatra\Bundle\SecurityBundle\SonatraSecurityBundle;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\SonatraSecurityExtension;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Complete Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class CompleteConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Load config file.
     *
     * @param ContainerBuilder $container
     * @param string           $file
     */
    abstract protected function loadFromFile(ContainerBuilder $container, $file);

    /**
     * Load config file of symfony security.
     *
     * @param ContainerBuilder $container
     * @param string           $file
     */
    abstract protected function loadSymfonyFromFile(ContainerBuilder $container, $file);

    public function testEntitiesClass()
    {
        $container = $this->getContainer('container1');

        $this->assertEquals('Acme\CoreBundle\Entity\User', $container->getParameter('sonatra_security.user_class'));
        $this->assertEquals('Acme\CoreBundle\Entity\Role', $container->getParameter('sonatra_security.role_class'));
        $this->assertEquals('Acme\CoreBundle\Entity\Group', $container->getParameter('sonatra_security.group_class'));
    }

    public function testCache()
    {
        $container = $this->getContainer('container1');

        $this->assertTrue(is_dir(sys_get_temp_dir().'/test_sonatra_security_bundle/'));
    }

    /**
     * Gets the container.
     *
     * @param string $file
     *
     * @return ContainerInterface
     */
    protected function getContainer($file)
    {
        $container = new ContainerBuilder();
        $baseSecurity = new SecurityExtension();
        $security = new SonatraSecurityExtension();
        $container->registerExtension($baseSecurity);
        $container->registerExtension($security);

        $container->setParameter('kernel.cache_dir', sys_get_temp_dir().'/test_sonatra_security_bundle/');

        $securityBundle = new SecurityBundle();
        $bundle = new SonatraSecurityBundle();

        $securityBundle->build($container); // Attach all default factories
        $bundle->build($container); // Attach all default factories

        $this->loadSymfonyFromFile($container, $file);
        $this->loadFromFile($container, $file);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/test_sonatra_security_bundle/');
    }

    /**
     * Clean up all.
     */
    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/test_sonatra_security_bundle/');
    }
}
