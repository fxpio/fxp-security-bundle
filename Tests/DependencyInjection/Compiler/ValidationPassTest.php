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
use Fxp\Component\Security\PermissionEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Validation Pass tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValidationPassTest extends TestCase
{
    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var ValidationPass
     */
    protected $compiler;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $this->compiler = new ValidationPass();
    }

    public function testProcessWithoutValidator()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('validator.builder')
            ->willReturn(false);

        $this->compiler->process($container);
    }

    public function testProcess()
    {
        $reflection = new \ReflectionClass(PermissionEvents::class);
        $dirname = dirname($reflection->getFileName());
        $permissionFile = realpath($dirname.'/Resources/config/validation/Permission.xml');
        $sharingFile = realpath($dirname.'/Resources/config/validation/Sharing.xml');

        $this->assertFileExists($permissionFile);
        $this->assertFileExists($sharingFile);

        $validator = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();

        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with('validator.builder')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with('validator.builder')
            ->willReturn($validator);

        $validator->expects($this->once())
            ->method('addMethodCall')
            ->with('addXmlMappings', [
                [
                    $permissionFile,
                    $sharingFile,
                ],
            ]);

        $this->compiler->process($this->container);
    }
}
