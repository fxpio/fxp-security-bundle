<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\Doctrine\ORM\Listener;

use Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener\SharingDeleteListenerContainerAware;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSharing;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sharing Delete Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingDeleteListenerContainerAwareTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPermissionManager()
    {
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('sonatra_security.sharing_manager')
            ->willReturn($sharingManager);

        $listener = new SharingDeleteListenerContainerAware(MockSharing::class);
        $listener->container = $container;

        $this->assertSame($sharingManager, $listener->getSharingManager());
        $this->assertNull($listener->container);
    }
}
