<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Listener;

use Sonatra\Component\Security\Event\GetExpressionVariablesEvent;
use Sonatra\Component\Security\ExpressionVariableEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * SecurityListener handles security restrictions on controllers.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityAnnotationSubscriber implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher         The event dispatcher
     * @param TokenStorageInterface    $tokenStorage       The token storage
     * @param ExpressionLanguage       $expressionLanguage The expression language
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                TokenStorageInterface $tokenStorage,
                                ExpressionLanguage $expressionLanguage)
    {
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }

    /**
     * On kernel controller action.
     *
     * @param FilterControllerEvent $event The event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_sonatra_security')) {
            return;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            throw new \LogicException('To use the @Security tag, your controller needs to be behind a firewall.');
        }

        if (!$this->expressionLanguage->evaluate($configuration->getExpression(), $this->getVariables($token, $request))) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Get the variables.
     *
     * @param TokenInterface $token   The token
     * @param Request        $request The request
     *
     * @return array
     */
    protected function getVariables(TokenInterface $token, Request $request)
    {
        $event = new GetExpressionVariablesEvent($token);
        $this->dispatcher->dispatch(ExpressionVariableEvents::GET, $event);

        $variables = array_merge(array(
            'object' => $request,
            'request' => $request,
        ), $event->getVariables(), $this->getRequestVariables($request));

        return $variables;
    }

    private function getRequestVariables(Request $request)
    {
        $variables = array();

        foreach ($request->attributes->all() as $key => $value) {
            if (false === strpos($key, '_')) {
                $variables[$key] = $value;
            }
        }

        return $variables;
    }
}
