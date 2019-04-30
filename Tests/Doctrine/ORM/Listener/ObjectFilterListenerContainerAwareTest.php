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

use Doctrine\ORM\Event\OnFlushEventArgs;
use Fxp\Bundle\SecurityBundle\Doctrine\ORM\Listener\ObjectFilterListenerContainerAware;
use Fxp\Component\Security\ObjectFilter\ObjectFilterInterface;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Object Filter Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class ObjectFilterListenerContainerAwareTest extends TestCase
{
    public function testOnFlush(): void
    {
        /** @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $objectFilter = $this->getMockBuilder(ObjectFilterInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage)
        ;

        $container->expects($this->at(1))
            ->method('get')
            ->with('fxp_security.permission_manager')
            ->willReturn($permissionManager)
        ;

        $container->expects($this->at(2))
            ->method('get')
            ->with('fxp_security.object_filter')
            ->willReturn($objectFilter)
        ;

        $listener = new ObjectFilterListenerContainerAware();
        $listener->container = $container;

        $listener->onFlush($args);

        $this->assertNull($listener->container);
    }
}
