<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\Doctrine\ORM\Listener;

use Fxp\Bundle\SecurityBundle\Doctrine\ORM\Listener\SharingDeleteListenerContainerAware;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sharing Delete Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class SharingDeleteListenerContainerAwareTest extends TestCase
{
    public function testGetPermissionManager(): void
    {
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('fxp_security.sharing_manager')
            ->willReturn($sharingManager)
        ;

        $listener = new SharingDeleteListenerContainerAware(MockSharing::class);
        $listener->container = $container;

        $this->assertSame($sharingManager, $listener->getSharingManager());
        $this->assertNull($listener->container);
    }
}
