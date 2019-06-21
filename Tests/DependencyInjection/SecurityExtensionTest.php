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
use PHPUnit\Framework\MockObject\MockObject;
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
 *
 * @internal
 */
final class SecurityExtensionTest extends TestCase
{
    /**
     * @var BaseSecurityExtension|MockObject
     */
    protected $baseExt;

    /**
     * @var SecurityExtension
     */
    protected $ext;

    protected function setUp(): void
    {
        $this->baseExt = $this->getMockBuilder(BaseSecurityExtension::class)->disableOriginalConstructor()->getMock();
        $this->ext = new SecurityExtension($this->baseExt);
    }

    public function testGetAlias(): void
    {
        $this->baseExt->expects(static::once())
            ->method('getAlias')
            ->willReturn('ALIAS')
        ;

        static::assertSame('ALIAS', $this->ext->getAlias());
    }

    public function testGetNamespace(): void
    {
        $this->baseExt->expects(static::once())
            ->method('getNamespace')
            ->willReturn('NAMESPACE')
        ;

        static::assertSame('NAMESPACE', $this->ext->getNamespace());
    }

    public function testGetXsdValidationBasePath(): void
    {
        $this->baseExt->expects(static::once())
            ->method('getXsdValidationBasePath')
            ->willReturn('XSD')
        ;

        static::assertSame('XSD', $this->ext->getXsdValidationBasePath());
    }

    public function testGetConfiguration(): void
    {
        /** @var ContainerBuilder $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $config = [];
        $configuration = $this->getMockBuilder(ConfigurationInterface::class)->getMock();

        $this->baseExt->expects(static::once())
            ->method('getConfiguration')
            ->with($config, $container)
            ->willReturn($configuration)
        ;

        static::assertSame($configuration, $this->ext->getConfiguration($config, $container));
    }

    public function testAddSecurityListenerFactory(): void
    {
        /** @var SecurityFactoryInterface $factory */
        $factory = $this->getMockBuilder(SecurityFactoryInterface::class)->getMock();

        $this->baseExt->expects(static::once())
            ->method('addSecurityListenerFactory')
            ->with($factory)
        ;

        $this->ext->addSecurityListenerFactory($factory);
    }

    public function testAddUserProviderFactory(): void
    {
        /** @var UserProviderFactoryInterface $factory */
        $factory = $this->getMockBuilder(UserProviderFactoryInterface::class)->getMock();

        $this->baseExt->expects(static::once())
            ->method('addUserProviderFactory')
            ->with($factory)
        ;

        $this->ext->addUserProviderFactory($factory);
    }

    public function testLoad(): void
    {
        /** @var ContainerBuilder|MockObject $container */
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

        $this->baseExt->expects(static::once())
            ->method('load')
            ->with($validConfigs, $container)
        ;

        $container->expects(static::once())
            ->method('setParameter')
            ->with('fxp_security.access_control', $accessControl)
        ;

        $this->ext->load($configs, $container);
    }

    public function testLoadWithoutControlAccess(): void
    {
        /** @var ContainerBuilder|MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $configs = [[
            'rule' => 'RULE',
            'access_control' => [],
            'KEY' => 'VALUE',
        ]];
        $validConfigs = [[
            'KEY' => 'VALUE',
        ]];

        $this->baseExt->expects(static::once())
            ->method('load')
            ->with($validConfigs, $container)
        ;

        $container->expects(static::never())
            ->method('setParameter')
        ;

        $this->ext->load($configs, $container);
    }
}
