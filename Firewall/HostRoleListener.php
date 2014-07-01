<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Inject the host role in existing token role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $context;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ListenerInterface
     */
    protected $anonymousListener;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface $context
     * @param array                    $config
     * @param ListenerInterface        $anonymousListener
     */
    public function __construct(SecurityContextInterface $context, array $config, ListenerInterface $anonymousListener)
    {
        $this->context = $context;
        $this->config = $config;
        $this->anonymousListener = $anonymousListener;
    }

    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if (0 === count($this->config)) {
            return;
        }

        $token = $this->context->getToken();
        $hostRole = null;
        $hostname = $event->getRequest()->getHttpHost();

        foreach ($this->config as $host => $role) {
            if (preg_match('/.'.$host.'/', $hostname)) {
                $hostRole = new Role($role);
                break;
            }
        }

        if (null === $hostRole) {
            return;
        }

        // add anonymous token
        if (null === $token) {
            $this->anonymousListener->handle($event);
            $token = $this->context->getToken();
        }

        $tRoles = $token->getRoles();
        $alreadyInclude = false;

        foreach ($tRoles as $role) {
            if ($hostRole->getRole() === $role->getRole()) {
                $alreadyInclude = true;
                break;
            }
        }

        //add anonymous role host on existing token
        if (!$alreadyInclude) {
            $tRoles[] = $hostRole;

            $ref = new \ReflectionClass($token);
            $prop = $ref->getParentClass()->getProperty('roles');
            $prop->setAccessible(true);
            $prop->setValue($token, $tRoles);
            $prop = $ref->getParentClass()->getProperty('authenticated');
            $prop->setAccessible(true);
            $prop->setValue($token, true);
        }

        $this->context->setToken($token);
    }
}
