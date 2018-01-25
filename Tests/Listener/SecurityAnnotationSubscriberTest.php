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
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
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
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

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
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $this->controller = function () {
            return new Response();
        };
        $this->listener = new SecurityAnnotationSubscriber(
            $this->dispatcher,
            $this->tokenStorage,
            $this->expression,
            $this->logger
        );

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testAddExpressionLanguageProvider()
    {
        /* @var ExpressionFunctionProviderInterface $provider */
        $provider = $this->getMockBuilder(ExpressionFunctionProviderInterface::class)->getMock();

        $this->expression->expects($this->once())
            ->method('registerProvider')
            ->with($provider);

        $this->listener->addExpressionLanguageProvider($provider);
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
        $request = $this->createRequest([new Security(['expression' => 'has_role("ROLE_ADMIN")'])]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->listener->onKernelController($event);
    }

    public function testOnKernelController()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest([new Security(['expression' => 'has_role("ROLE_ADMIN")'])]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', ['object' => $request, 'request' => $request, 'subject' => $request])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

                return true;
            });

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerWithRequestVariables()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest([new Security(['expression' => 'has_role("ROLE_ADMIN")'])]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $request->attributes->add([
            'foo' => 'bar',
        ]);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', [
                'object' => $request,
                'request' => $request,
                'subject' => $request,
                'foo' => 'bar',
            ])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

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
        $request = $this->createRequest([new Security(['expression' => 'has_role("ROLE_ADMIN")'])]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', ['object' => $request, 'request' => $request, 'subject' => $request])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

                return false;
            });

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerWithMultipleAnnotations()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest([
            new Security(['expression' => 'has_role("ROLE_USER")']),
            new Security(['expression' => 'has_role("ROLE_ADMIN")']),
        ]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('(has_role("ROLE_USER")) and (has_role("ROLE_ADMIN"))', ['object' => $request, 'request' => $request, 'subject' => $request])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('(has_role("ROLE_USER")) and (has_role("ROLE_ADMIN"))', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

                return true;
            });

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerWithOverridePreviousAnnotation()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest([
            new Security(['expression' => 'has_role("ROLE_USER")']),
            new Security(['expression' => 'has_role("ROLE_ADMIN")', 'override' => true]),
        ]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', ['object' => $request, 'request' => $request, 'subject' => $request])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

                return true;
            });

        $this->listener->onKernelController($event);
    }

    public function testOnKernelControllerWithCollidedVariables()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $request = $this->createRequest([new Security(['expression' => 'has_role("ROLE_ADMIN")'])]);
        $event = new FilterControllerEvent($this->kernel, $this->controller, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->dispatcher->addListener(ExpressionVariableEvents::GET, function (GetExpressionVariablesEvent $event) use ($token) {
            $this->assertSame($token, $event->getToken());
            $event->addVariable('token', $token);
        });

        $this->expression->expects($this->once())
            ->method('evaluate')
            ->with('has_role("ROLE_ADMIN")', ['object' => $request, 'request' => $request, 'subject' => $request, 'token' => $token])
            ->willReturnCallback(function ($expression, $variables) {
                $this->assertSame('has_role("ROLE_ADMIN")', $expression);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('request', $variables);
                $this->assertArrayHasKey('subject', $variables);

                return true;
            });

        $request->attributes->set('token', 'duplicate_token_variable');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Controller argument "token" collided with the built-in Fxp Security expression variables. The built-in values are being used for the @Security expression.');

        $this->listener->onKernelController($event);
    }

    /**
     * Create the request.
     *
     * @param Security[] $security The security annotations
     *
     * @return Request
     */
    private function createRequest(array $security = [])
    {
        return new Request([], [], [
            '_fxp_security' => $security,
        ]);
    }
}
