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
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy as BaseSecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Strategy for retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityRetrievalStrategy extends BaseSecurityIdentityRetrievalStrategy
{
    /**
     * @var Symfony\Component\Security\Core\Role\RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var array
     */
    private $cache = array();

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

        $this->roleHierarchy = $roleHierarchy;
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        if (null === $token) {
            return array();
        }

        if (array_key_exists($token->getUsername(), $this->cache)) {
            return $this->cache[$token->getUsername()];
        }

        $sids = array();
        $em = $this->registry->getManager();
        $filterIsEnabled = $em->getFilters()->isEnabled('sonatra_acl');

        if ($filterIsEnabled) {
            $em->getFilters()->disable('sonatra_acl');
        }

        if ($token->getUser() instanceof UserInterface) {
            $userRoles = array();

            foreach ($token->getUser()->getRoles() as $role) {
                $userRoles[] = new Role($role);
            }

            foreach ($this->roleHierarchy->getReachableRoles($userRoles) as $role) {
                $sids[] = new RoleSecurityIdentity($role);
            }
        }

        $sids = array_merge($sids, parent::getSecurityIdentities($token));

        $sids = array_unique($sids);
        $this->cache[$token->getUsername()] = $sids;

        if ($filterIsEnabled) {
            $em->getFilters()->enable('sonatra_acl');
        }

        return $sids;
    }
}
