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

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\TranslatorPass;
use Fxp\Component\Security\PermissionContexts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Translator Pass tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class TranslatorPassTest extends TestCase
{
    /**
     * @var ContainerBuilder|MockObject
     */
    protected $container;

    /**
     * @var TranslatorPass
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $this->compiler = new TranslatorPass();
    }

    public function testProcessWithoutTranslator(): void
    {
        /** @var ContainerBuilder|MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('translator.default')
            ->willReturn(false)
        ;

        $this->compiler->process($container);
    }

    public function testProcess(): void
    {
        $reflection = new \ReflectionClass(PermissionContexts::class);
        $dirname = \dirname($reflection->getFileName());
        $file = realpath($dirname.'/Resources/config/translations/validators.en.xlf');

        $this->assertFileExists($file);

        $translator = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();

        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with('translator.default')
            ->willReturn(true)
        ;

        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with('translator.default')
            ->willReturn($translator)
        ;

        $translator->expects($this->once())
            ->method('getArguments')
            ->willReturn([null, null, [], []])
        ;

        $translator->expects($this->once())
            ->method('getArgument')
            ->with(3)
            ->willReturn([])
        ;

        $translator->expects($this->once())
            ->method('replaceArgument')
            ->with(3, [
                'resource_files' => [
                    'en' => [
                        $file,
                    ],
                ],
            ])
        ;

        $this->compiler->process($this->container);
    }
}
