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
use Fxp\Bundle\SecurityBundle\Doctrine\ORM\Listener\PermissionCheckerListenerContainerAware;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Object Filter Listener Container Aware Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionCheckerListenerContainerAwareTest extends TestCase
{
    public function testOnFlush(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects(static::at(0))
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage)
        ;

        $container->expects(static::at(1))
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($authChecker)
        ;

        $container->expects(static::at(2))
            ->method('get')
            ->with('fxp_security.permission_manager')
            ->willReturn($permissionManager)
        ;

        $listener = new PermissionCheckerListenerContainerAware();
        $listener->container = $container;

        $listener->onFlush($args);

        static::assertNull($listener->container);
    }
}
