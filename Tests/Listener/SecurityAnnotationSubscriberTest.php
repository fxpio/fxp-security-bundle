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

use Fxp\Bundle\SecurityBundle\Configuration\Security;
use Fxp\Bundle\SecurityBundle\Listener\SecurityAnnotationSubscriber;
use Fxp\Component\Security\Event\GetExpressionVariablesEvent;
use Fxp\Component\Security\ExpressionVariableEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;

/**
 * Security annotation subscriber tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SecurityAnnotationSubscriberTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var ExpressionLanguage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $expression;

    /**
     * @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    /**
     * @var callable
     */
    protected $controller;

    /**
     * @var SecurityAnnotationSubscriber
     */
    protected $listener;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->expression = $this->getMockBuilder(ExpressionLanguage::class)->disableOriginalConstructor()->getMock();
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $this->controller = function () {
            return new Response();
        };
        $this->listener = new SecurityAnnotationSubscriber(
            $this->dispatcher,
            $this->tokenStorage,
            $this->expression
        );

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testOnKernelControllerWithoutAnnotation()
    {
        $request = $this->createRequest();
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->never())
            ->method('getToken');

        $this->listener->onKernelController($event);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage To use the @Security tag, your controller needs to be behind a firewall.
     */
    public function testOnKernelControllerWithoutToken()
    {
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN")')));
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->listener->onKernelController($event);
    }

    public function testOnKernelController()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN")')));
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', array('object' => $request, 'request' => $request))
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);

                return true;
            });

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerWithRequestVariables()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN")')));
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $request->attributes->add(array(
            'foo' => 'bar',
        ));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', array(
                'object' => $request,
                'request' => $request,
                'foo' => 'bar',
            ))
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);

                return true;
            });

        $this->listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Access Denied
     */
    public function testOnKernelControllerWithAccessDeniedException()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN")')));
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', array('object' => $request, 'request' => $request))
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);

                return false;
            });

        $this->listener->onKernelController($event);
    }

    /**
     * Create the request.
     *
     * @param Security|null $security The security annotation
     *
     * @return Request
     */
    private function createRequest(Security $security = null)
    {
        return new Request(array(), array(), array(
            '_fxp_security' => $security,
        ));
    }
}
