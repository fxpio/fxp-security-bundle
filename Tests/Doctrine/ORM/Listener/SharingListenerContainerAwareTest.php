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
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('sonatra_security.permission_manager')
            ->willReturn($permissionManager);

        $container->expects($this->at(1))
            ->method('get')
            ->with('sonatra_security.sharing_manager')
            ->willReturn($sharingManager);

        $container->expects($this->at(2))
            ->method('get')
            ->with('event_dispatcher')
            ->willReturn($dispatcher);

        $container->expects($this->at(3))
            ->method('get')
            ->with('sonatra_security.security_identity_manager')
            ->willReturn($sidManager);

        $container->expects($this->at(4))
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage);

        $listener = new SharingListenerContainerAware();
        $listener->container = $container;

        $this->assertSame($permissionManager, $listener->getPermissionManager());
        $this->assertNull($listener->container);
    }
}
