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

use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent;
use Sonatra\Bundle\SecurityBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var OrganizationalContextInterface|null
     */
    private $context;

    /**
     * @var array
     */
    private $cacheExec = array();

    /**
     * Constructor.
     *
     * @param RoleHierarchyInterface      $roleHierarchy
     * @param AuthenticationTrustResolver $authenticationTrustResolver
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy, AuthenticationTrustResolver $authenticationTrustResolver)
    {
        $this->roleHierarchy = $roleHierarchy;

        parent::__construct($roleHierarchy, $authenticationTrustResolver);
    }

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
     * Set the organizational context.
     *
     * @param OrganizationalContextInterface $context The organizational context
     */
    public function setOrganizationalContext(OrganizationalContextInterface $context)
    {
        $this->context = $context;
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
        if (!$token instanceof AnonymousToken) {
            // dispatch pre event
            if (null !== $this->eventDispatcher) {
                $event = new SecurityIdentityEvent();
                $event->setSecurityIdentities($sids);
                $event = $this->eventDispatcher->dispatch(Events::PRE_SECURITY_IDENTITY_RETRIEVAL, $event);
                $sids = $event->getSecurityIdentities();
            }

            $sids = $this->mergeSecurityIdentities($sids, $token, 'Sonatra\Bundle\SecurityBundle\Acl\Domain\OrganizationSecurityIdentity');
            $sids = $this->mergeSecurityIdentities($sids, $token, 'Sonatra\Bundle\SecurityBundle\Acl\Domain\GroupSecurityIdentity');

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

    /**
     * Merge the security identities.
     *
     * @param array          $sids  The security identities
     * @param TokenInterface $token The token
     * @param string         $class The OrganizationSecurityIdentity ou GroupSecurityIdentity class name
     *
     * @return array The security identities
     */
    protected function mergeSecurityIdentities(array $sids, TokenInterface $token, $class)
    {
        try {
            /* @var OrganizationSecurityIdentity|GroupSecurityIdentity $class */
            $sids = array_merge($sids, $class::fromToken($token, $this->context, $this->roleHierarchy));
        } catch (\InvalidArgumentException $invalid) {
            // ignore, group has no group security identity
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
        $id = spl_object_hash($token);

        if (null !== $this->context) {
            $org = $this->context->getCurrentOrganization();

            if (null !== $org) {
                $id .= '_'.$org->getId();
            }
        }

        return $id;
    }
}
