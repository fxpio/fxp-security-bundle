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

use Sonatra\Bundle\SecurityBundle\Acl\Domain\OrganizationSecurityIdentity;
use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent;
use Sonatra\Bundle\SecurityBundle\IdentityRetrievalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for add organization security identity from token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationStrategyIdentitySubscriber implements EventSubscriberInterface, EventStrategyIdentityInterface
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var OrganizationalContextInterface
     */
    private $context;

    /**
     * Constructor.
     *
     * @param RoleHierarchyInterface         $roleHierarchy
     * @param OrganizationalContextInterface $context
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy,
                                OrganizationalContextInterface $context)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::ADD => array('addOrganizationSecurityIdentities', 0),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        $org = $this->context->getCurrentOrganization();

        return null !== $org
            ? '_'.$org->getId()
            : '';
    }

    /**
     * Add organization security identities.
     *
     * @param SecurityIdentityEvent $event
     */
    public function addOrganizationSecurityIdentities(SecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = array_merge($sids, OrganizationSecurityIdentity::fromToken($event->getToken(),
                $this->context, $this->roleHierarchy));
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
