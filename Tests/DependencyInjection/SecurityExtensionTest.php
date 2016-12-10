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

use Sonatra\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension as BaseSecurityExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Symfony security extension tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseSecurityExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseExt;

    /**
     * @var SecurityExtension
     */
    protected $ext;

    protected function setUp()
    {
        $this->baseExt = $this->getMockBuilder(BaseSecurityExtension::class)->disableOriginalConstructor()->getMock();
        $this->ext = new SecurityExtension($this->baseExt);
    }

    public function testGetAlias()
    {
        $this->baseExt->expects($this->once())
            ->method('getAlias')
            ->willReturn('ALIAS');

        $this->assertSame('ALIAS', $this->ext->getAlias());
    }

    public function testGetNamespace()
    {
        $this->baseExt->expects($this->once())
            ->method('getNamespace')
            ->willReturn('NAMESPACE');

        $this->assertSame('NAMESPACE', $this->ext->getNamespace());
    }

    public function testGetXsdValidationBasePath()
    {
        $this->baseExt->expects($this->once())
            ->method('getXsdValidationBasePath')
            ->willReturn('XSD');

        $this->assertSame('XSD', $this->ext->getXsdValidationBasePath());
    }

    public function testGetClassesToCompile()
    {
        $value = array(
            'CLASS',
        );
        $this->baseExt->expects($this->once())
            ->method('getClassesToCompile')
            ->willReturn($value);

        $this->assertSame($value, $this->ext->getClassesToCompile());
    }

    public function testGetConfiguration()
    {
        /* @var ContainerBuilder $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $config = array();
        $configuration = $this->getMockBuilder(ConfigurationInterface::class)->getMock();

        $this->baseExt->expects($this->once())
            ->method('getConfiguration')
            ->with($config, $container)
            ->willReturn($configuration);

        $this->assertSame($configuration, $this->ext->getConfiguration($config, $container));
    }

    public function testAddSecurityListenerFactory()
    {
        /* @var SecurityFactoryInterface $factory */
        $factory = $this->getMockBuilder(SecurityFactoryInterface::class)->getMock();

        $this->baseExt->expects($this->once())
            ->method('addSecurityListenerFactory')
            ->with($factory);

        $this->ext->addSecurityListenerFactory($factory);
    }

    public function testAddUserProviderFactory()
    {
        /* @var UserProviderFactoryInterface $factory */
        $factory = $this->getMockBuilder(UserProviderFactoryInterface::class)->getMock();

        $this->baseExt->expects($this->once())
            ->method('addUserProviderFactory')
            ->with($factory);

        $this->ext->addUserProviderFactory($factory);
    }

    public function testLoad()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $accessControl = array(
            array(
                'path' => '^/path/',
                'allow_if' => 'has_role("ROLE_ADMIN")',
                'requires_channel' => null,
                'host' => null,
                'ips' => array(),
                'methods' => array(),
                'roles' => array(),
            ),
        );
        $configs = array(array(
            'rule' => 'RULE',
            'access_control' => $accessControl,
            'KEY' => 'VALUE',
        ));
        $validConfigs = array(array(
            'KEY' => 'VALUE',
        ));

        $this->baseExt->expects($this->once())
            ->method('load')
            ->with($validConfigs, $container);

        $container->expects($this->once())
            ->method('setParameter')
            ->with('sonatra_security.access_control', $accessControl);

        $this->ext->load($configs, $container);
    }

    public function testLoadWithoutControlAccess()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $configs = array(array(
            'rule' => 'RULE',
            'access_control' => array(),
            'KEY' => 'VALUE',
        ));
        $validConfigs = array(array(
            'KEY' => 'VALUE',
        ));

        $this->baseExt->expects($this->once())
            ->method('load')
            ->with($validConfigs, $container);

        $container->expects($this->never())
            ->method('setParameter');

        $this->ext->load($configs, $container);
    }
}
