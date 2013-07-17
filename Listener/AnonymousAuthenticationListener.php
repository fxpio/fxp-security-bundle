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

use Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener as BaseListener;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Replace the standard anonymous authentication for include the role on the
 * anonymous token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AnonymousAuthenticationListener extends BaseListener
{
    protected $context;
    protected $key;
    protected $logger;
    protected $container;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface $context
     * @param string                   $key
     * @param LoggerInterface          $logger
     * @param ContainerInterface       $container
     */
    public function __construct(SecurityContextInterface $context, $key, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->context = $context;
        $this->key = $key;
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        $token = $this->context->getToken();
        $roles = array();
        $anonymousRole = null;
        $hostname = '';
        $rolesForHosts = $this->container->getParameter('sonatra_security.anonymous_authentication.hosts');

        if ($this->container->isScopeActive('request')) {
            $hostname = $this->container->get('request')->getHttpHost();
        }

        foreach ($rolesForHosts as $host => $role) {
            if (preg_match('/.'.$host.'/', $hostname)) {
                $anonymousRole = $role;
                break;
            }
        }

        // find role for anonymous
        if (null !== $anonymousRole) {
            $roles[] = new Role($anonymousRole);
        }

        if (empty($roles)) {
            return;
        }

        // add anonymous token
        if (null === $token) {
            $this->context->setToken(new AnonymousToken($this->key, 'anon.', $roles));

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Populated SecurityContext with an anonymous Token using role '.$anonymousRole.' for host '.$hostname));
            }

            return;
        }

        //add anonymous role on existing token
        $ref = new \ReflectionClass($token);
        $prop = $ref->getParentClass()->getProperty('roles');
        $tRoles = $token->getRoles();

        foreach ($roles as $role) {
            if (!in_array($role, $tRoles)) {
                $tRoles[] = $role;
            }
        }

        $prop->setAccessible(true);
        $prop->setValue($token, $tRoles);

        $prop = $ref->getParentClass()->getProperty('authenticated');
        $prop->setAccessible(true);
        $prop->setValue($token, true);

        $this->context->setToken($token);
    }
}
