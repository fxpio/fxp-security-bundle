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

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy as BaseSecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Strategy for retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityRetrievalStrategy extends BaseSecurityIdentityRetrievalStrategy
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * Constructor
     *
     * @param RoleHierarchyInterface      $roleHierarchy
     * @param AuthenticationTrustResolver $authenticationTrustResolver
     * @param RegistryInterface           $registry
    */
    public function __construct(RoleHierarchyInterface $roleHierarchy,
            AuthenticationTrustResolver $authenticationTrustResolver,
            RegistryInterface $registry)
    {
        parent::__construct($roleHierarchy, $authenticationTrustResolver);

        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        $sids = parent::getSecurityIdentities($token);
        $em = $this->registry->getManager();
        $filterIsEnabled = $em->getFilters()->isEnabled('sonatra_acl');

        if ($filterIsEnabled) {
            $em->getFilters()->disable('sonatra_acl');
        }

        // add group security identity
        if (!$token instanceof AnonymousToken) {
            try {
                $sids = array_merge($sids, GroupSecurityIdentity::fromToken($token));

            } catch (\InvalidArgumentException $invalid) {
                // ignore, group has no group security identity
            }
        }

        if ($filterIsEnabled) {
            $em->getFilters()->enable('sonatra_acl');
        }

        return $sids;
    }
}
