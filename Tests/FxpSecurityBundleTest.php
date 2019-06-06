<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests;

use Fxp\Bundle\SecurityBundle\FxpSecurityBundle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Security bundle tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class FxpSecurityBundleTest extends TestCase
{
    public function testSecurityBundleNotRegistered(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\LogicException::class);
        $this->expectExceptionMessage('The FxpSecurityBundle must be registered after the SecurityBundle in your App Kernel');

        /** @var ContainerBuilder|MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(false)
        ;

        $bundle = new FxpSecurityBundle();
        $bundle->build($container);
    }
}
