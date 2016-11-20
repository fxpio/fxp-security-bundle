<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests;

use Sonatra\Bundle\SecurityBundle\SonatraSecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Security bundle tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonatra\Component\Security\Exception\LogicException
     * @expectedExceptionMessage The SonatraSecurityBundle must be registered after the SecurityBundle in your App Kernel
     */
    public function testSecurityBundleNotRegistered()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(false);

        $bundle = new SonatraSecurityBundle();
        $bundle->build($container);
    }
}
