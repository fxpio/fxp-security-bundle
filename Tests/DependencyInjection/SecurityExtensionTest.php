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

use Fxp\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension as BaseSecurityExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Symfony security extension tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SecurityExtensionTest extends TestCase
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

    public function testGetConfiguration()
    {
        /* @var ContainerBuilder $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $config = [];
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
        $accessControl = [
            [
                'path' => '^/path/',
                'allow_if' => 'has_role("ROLE_ADMIN")',
                'requires_channel' => null,
                'host' => null,
                'ips' => [],
                'methods' => [],
                'roles' => [],
            ],
        ];
        $configs = [[
            'rule' => 'RULE',
            'access_control' => $accessControl,
            'KEY' => 'VALUE',
        ]];
        $validConfigs = [[
            'KEY' => 'VALUE',
        ]];

        $this->baseExt->expects($this->once())
            ->method('load')
            ->with($validConfigs, $container);

        $container->expects($this->once())
            ->method('setParameter')
            ->with('fxp_security.access_control', $accessControl);

        $this->ext->load($configs, $container);
    }

    public function testLoadWithoutControlAccess()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $configs = [[
            'rule' => 'RULE',
            'access_control' => [],
            'KEY' => 'VALUE',
        ]];
        $validConfigs = [[
            'KEY' => 'VALUE',
        ]];

        $this->baseExt->expects($this->once())
            ->method('load')
            ->with($validConfigs, $container);

        $container->expects($this->never())
            ->method('setParameter');

        $this->ext->load($configs, $container);
    }
}
