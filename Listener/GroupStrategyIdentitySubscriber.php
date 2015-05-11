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

use Sonatra\Bundle\SecurityBundle\Acl\Domain\GroupSecurityIdentity;
use Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent;
use Sonatra\Bundle\SecurityBundle\IdentityRetrievalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for add group security identity from token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupStrategyIdentitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::ADD => array('addGroupSecurityIdentities', 0),
        );
    }

    /**
     * Add group security identities.
     *
     * @param SecurityIdentityEvent $event
     */
    public function addGroupSecurityIdentities(SecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = array_merge($sids, GroupSecurityIdentity::fromToken($event->getToken()));
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
