<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\DependencyInjection\Compiler;

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ValidationPass;
use Fxp\Component\Security\PermissionContexts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Validation Pass tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ValidationPassTest extends TestCase
{
    /**
     * @var ContainerBuilder|MockObject
     */
    protected $container;

    /**
     * @var ValidationPass
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $this->compiler = new ValidationPass();
    }

    public function testProcessWithoutValidator(): void
    {
        /** @var ContainerBuilder|MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects(static::once())
            ->method('hasDefinition')
            ->with('validator.builder')
            ->willReturn(false)
        ;

        $this->compiler->process($container);
    }

    public function testProcess(): void
    {
        $reflection = new \ReflectionClass(PermissionContexts::class);
        $dirname = \dirname($reflection->getFileName());
        $permissionFile = realpath($dirname.'/Resources/config/validation/Permission.xml');
        $sharingFile = realpath($dirname.'/Resources/config/validation/Sharing.xml');

        static::assertFileExists($permissionFile);
        static::assertFileExists($sharingFile);

        $validator = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();

        $this->container->expects(static::once())
            ->method('hasDefinition')
            ->with('validator.builder')
            ->willReturn(true)
        ;

        $this->container->expects(static::once())
            ->method('getDefinition')
            ->with('validator.builder')
            ->willReturn($validator)
        ;

        $validator->expects(static::once())
            ->method('addMethodCall')
            ->with('addXmlMappings', [
                [
                    $permissionFile,
                    $sharingFile,
                ],
            ])
        ;

        $this->compiler->process($this->container);
    }
}
