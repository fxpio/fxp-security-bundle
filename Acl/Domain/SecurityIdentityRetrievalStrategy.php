<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent;
use Sonatra\Bundle\SecurityBundle\IdentityRetrievalEvents;
use Sonatra\Bundle\SecurityBundle\Listener\EventStrategyIdentityInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy as BaseSecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Strategy for retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityRetrievalStrategy extends BaseSecurityIdentityRetrievalStrategy
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $cacheExec = array();

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface    $dispatcher
     * @param RoleHierarchyInterface      $roleHierarchy
     * @param AuthenticationTrustResolver $authenticationTrustResolver
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                RoleHierarchyInterface $roleHierarchy,
                                AuthenticationTrustResolver $authenticationTrustResolver)
    {
        $this->dispatcher = $dispatcher;

        parent::__construct($roleHierarchy, $authenticationTrustResolver);
    }

    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache()
    {
        $this->cacheExec = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        $id = $this->buildId($token);

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        /* @var SecurityIdentityEvent $event */
        $event = null;
        $sids = parent::getSecurityIdentities($token);

        // add group security identity
        if ($token instanceof TokenInterface && !$token instanceof AnonymousToken) {
            // dispatch pre event
            $event = new SecurityIdentityEvent($token);
            $event->setSecurityIdentities($sids);
            $this->dispatcher->dispatch(IdentityRetrievalEvents::PRE, $event);
            // dispatch add event
            $event->setSecurityIdentities($sids);
            $this->dispatcher->dispatch(IdentityRetrievalEvents::ADD, $event);
            $sids = $event->getSecurityIdentities();
            // dispatch post event
            $this->dispatcher->dispatch(IdentityRetrievalEvents::POST, $event);

            $this->cacheExec[$id] = $sids;
        }

        return $sids;
    }

    /**
     * Build the unique identifier for execution cache.
     *
     * @param TokenInterface $token The token
     *
     * @return string
     */
    protected function buildId(TokenInterface $token)
    {
        /* @var EventSubscriberInterface[] $listeners */
        $listeners = $this->dispatcher->getListeners(IdentityRetrievalEvents::ADD);
        $id = spl_object_hash($token);

        foreach ($listeners as $listener) {
            if ($listener instanceof EventStrategyIdentityInterface) {
                $id .= '_'.$listener->getCacheId();
            }
        }

        return $id;
    }
}
