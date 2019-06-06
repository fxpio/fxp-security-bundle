<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Listener;

use Fxp\Bundle\SecurityBundle\Configuration\Security;
use Fxp\Component\Security\Event\GetExpressionVariablesEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * SecurityListener handles security restrictions on controllers.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
     * @var null|string
     */
    private $prefixRenameArguments;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher            The event dispatcher
     * @param TokenStorageInterface    $tokenStorage          The token storage
     * @param ExpressionLanguage       $expressionLanguage    The expression language
     * @param null|string              $prefixRenameArguments Check if the controller arguments can be renamed in conflict
     * @param null|LoggerInterface     $logger                The logger
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage,
        ExpressionLanguage $expressionLanguage,
        ?string $prefixRenameArguments = null,
        ?LoggerInterface $logger = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->expressionLanguage = $expressionLanguage;
        $this->prefixRenameArguments = $prefixRenameArguments;
        $this->logger = $logger;
    }

    /**
     * Add the expression function provider.
     *
     * @param ExpressionFunctionProviderInterface $provider The expression function provider
     */
    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider): void
    {
        $this->expressionLanguage->registerProvider($provider);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    /**
     * On kernel controller action.
     *
     * @param ControllerEvent $event The event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (empty($expression = $this->getExpression($request))) {
            return;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            throw new \LogicException('To use the @Security tag, your controller needs to be behind a firewall.');
        }

        if (!$this->expressionLanguage->evaluate($expression, $this->getVariables($token, $request))) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Get the expression.
     *
     * @param Request $request The request
     *
     * @return string
     */
    protected function getExpression(Request $request): string
    {
        /** @var Security[] $configurations */
        $configurations = $request->attributes->get('_fxp_security', []);
        $expressions = [];

        foreach ($configurations as $configuration) {
            if ($configuration->isOverriding()) {
                $expressions = [];
            }
            $expressions[] = $configuration->getExpression();
        }

        return \count($expressions) > 1 ? '('.implode(') and (', $expressions).')' : implode('', $expressions);
    }

    /**
     * Get the variables.
     *
     * @param TokenInterface $token   The token
     * @param Request        $request The request
     *
     * @return array
     */
    protected function getVariables(TokenInterface $token, Request $request): array
    {
        $event = new GetExpressionVariablesEvent($token);
        $this->dispatcher->dispatch($event);

        $variables = array_merge([
            'object' => $request,
            'subject' => $request,
            'request' => $request,
        ], $event->getVariables());

        return $this->mergeRequestVariables($variables, $this->getRequestVariables($request));
    }

    /**
     * Get the variables for request.
     *
     * @param Request $request The request
     *
     * @return array
     */
    private function getRequestVariables(Request $request): array
    {
        $variables = [];

        foreach ($request->attributes->all() as $key => $value) {
            if (false === strpos($key, '_')) {
                $variables[$key] = $value;
            }
        }

        return $variables;
    }

    /**
     * Validate and merge the request variables with the built-in security variables.
     *
     * @param array $variables
     * @param array $requestVariables
     *
     * @return array
     */
    private function mergeRequestVariables(array $variables, array $requestVariables): array
    {
        if ($diff = array_intersect(array_keys($variables), array_keys($requestVariables))) {
            foreach ($diff as $key => $variableName) {
                if ($variables[$variableName] === $requestVariables[$variableName]) {
                    unset($diff[$key]);
                }
            }

            if (!empty($diff)) {
                $requestVariables = $this->cleanRequestVariables($requestVariables, $diff);
            }
        }

        return array_merge($requestVariables, $variables);
    }

    /**
     * Clean the request variables.
     *
     * @param array    $variables The request variables
     * @param string[] $diff      The list of variable names in conflict
     *
     * @return array
     */
    private function cleanRequestVariables(array $variables, array $diff): array
    {
        if (null !== $this->prefixRenameArguments) {
            foreach ($diff as $name) {
                $variables[$this->prefixRenameArguments.$name] = $variables[$name];
                unset($variables[$name]);
            }
        } elseif (null !== $this->logger) {
            $singular = 1 === \count($diff);
            $this->logger->warning(sprintf('Controller argument%s "%s" collided with the built-in Fxp Security expression variables. The built-in value%s are being used for the @Security expression.', $singular ? '' : 's', implode('", "', $diff), $singular ? 's' : ''));
        }

        return $variables;
    }
}
