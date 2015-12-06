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

use Sonatra\Bundle\SecurityBundle\IdentityRetrievalEvents;
use Sonatra\Bundle\SecurityBundle\ReachableRoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;

/**
 * Listener for disable/re-enable the acl doctrine orm filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisableAclListener implements EventSubscriberInterface
{
    /**
     * @var AclManagerInterface
     */
    protected $aclManager;

    /**
     * Constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::PRE => array('disableAcl', -255),
            ReachableRoleEvents::PRE => array('disableAcl', -255),
            IdentityRetrievalEvents::POST => array('enableAcl', 255),
            ReachableRoleEvents::POST => array('enableAcl', 255),
        );
    }

    /**
     * Disable the acl.
     *
     * @param GenericEvent $event
     */
    public function disableAcl(GenericEvent $event)
    {
        $isEnabled = !$this->aclManager->isDisabled();
        $event->setArgument('sonatraSecurityAclIsEnabled', $isEnabled);

        if ($isEnabled) {
            $this->aclManager->disable();
        }
    }

    /**
     * Enable the acl.
     *
     * @param GenericEvent $event
     */
    public function enableAcl(GenericEvent $event)
    {
        if ($event->getArgument('sonatraSecurityAclIsEnabled')) {
            $this->aclManager->enable();
        }
    }
}
