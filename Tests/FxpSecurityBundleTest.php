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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Security bundle tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpSecurityBundleTest extends TestCase
{
    /**
     * @expectedException \Fxp\Component\Security\Exception\LogicException
     * @expectedExceptionMessage The FxpSecurityBundle must be registered after the SecurityBundle in your App Kernel
     */
    public function testSecurityBundleNotRegistered()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(false);

        $bundle = new FxpSecurityBundle();
        $bundle->build($container);
    }
}
