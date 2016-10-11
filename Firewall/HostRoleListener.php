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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Inject the host role in existing token role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ListenerInterface
     */
    protected $anonymousListener;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param array                 $config
     * @param ListenerInterface     $anonymousListener
     */
    public function __construct(TokenStorageInterface $tokenStorage, array $config, ListenerInterface $anonymousListener)
    {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->anonymousListener = $anonymousListener;
    }

    /**
     * Set if the listener is enabled.
     *
     * @param bool $enabled The value
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * Check if the listener is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $hostRole = $this->getHostRole($event);

        if (null === $hostRole) {
            return;
        }

        $token = $this->getToken($event);

        if (null === $token) {
            return;
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

        $this->tokenStorage->setToken($token);
    }

    /**
     * Get the host role.
     *
     * @param GetResponseEvent $event The response event
     *
     * @return Role|null
     */
    protected function getHostRole(GetResponseEvent $event)
    {
        $hostRole = null;
        $hostname = $event->getRequest()->getHttpHost();

        foreach ($this->config as $host => $role) {
            if (preg_match('/.'.$host.'/', $hostname)) {
                $hostRole = new Role($role);
                break;
            }
        }

        return $hostRole;
    }

    /**
     * Get the token.
     *
     * @param GetResponseEvent $event The response event
     *
     * @return null|TokenInterface
     */
    protected function getToken(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        // add anonymous token
        if (null === $token) {
            $this->anonymousListener->handle($event);
            $token = $this->tokenStorage->getToken();
        }

        return $token;
    }
}
