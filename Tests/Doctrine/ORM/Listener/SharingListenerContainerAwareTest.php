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

use Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener\SharingListenerContainerAware;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sharing Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingListenerContainerAwareTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPermissionManager()
    {
        $permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('sonatra_security.permission_manager')
            ->willReturn($permissionManager);

        $listener = new SharingListenerContainerAware();
        $listener->container = $container;

        $this->assertSame($permissionManager, $listener->getPermissionManager());
        $this->assertNull($listener->container);
    }
}
