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

use Doctrine\ORM\Event\OnFlushEventArgs;
use Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener\ObjectFilterListenerContainerAware;
use Sonatra\Component\Security\ObjectFilter\ObjectFilterInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Object Filter Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterListenerContainerAwareTest extends \PHPUnit_Framework_TestCase
{
    public function testOnFlush()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $objectFilter = $this->getMockBuilder(ObjectFilterInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage);

        $container->expects($this->at(1))
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($authorizationChecker);

        $container->expects($this->at(2))
            ->method('get')
            ->with('sonatra_security.permission_manager')
            ->willReturn($permissionManager);

        $container->expects($this->at(3))
            ->method('get')
            ->with('sonatra_security.object_filter')
            ->willReturn($objectFilter);

        $listener = new ObjectFilterListenerContainerAware();
        $listener->container = $container;

        $listener->onFlush($args);

        $this->assertNull($listener->container);
    }
}
