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
use Sonatra\Bundle\SecurityBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy as BaseSecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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
    private $eventDispatcher;

    /**
     * @var array
     */
    private $cacheExec = array();

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        $id = spl_object_hash($token);

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        /* @var SecurityIdentityEvent $event */
        $event = null;
        $sids = parent::getSecurityIdentities($token);

        // add group security identity
        if (!$token instanceof AnonymousToken) {
            // dispatch pre event
            if (null !== $this->eventDispatcher) {
                $event = new SecurityIdentityEvent();
                $event->setSecurityIdentities($sids);
                $event = $this->eventDispatcher->dispatch(Events::PRE_SECURITY_IDENTITY_RETRIEVAL, $event);
                $sids = $event->getSecurityIdentities();
            }

            try {
                $sids = array_merge($sids, GroupSecurityIdentity::fromToken($token));

            } catch (\InvalidArgumentException $invalid) {
                // ignore, group has no group security identity
            }

            // dispatch post event
            if (null !== $this->eventDispatcher) {
                $event->setSecurityIdentities($sids);
                $event = $this->eventDispatcher->dispatch(Events::POST_SECURITY_IDENTITY_RETRIEVAL, $event);
                $sids = $event->getSecurityIdentities();
            }

            $this->cacheExec[$id] = $sids;
        }

        return $sids;
    }
}
