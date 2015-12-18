<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Expression;

use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRoleEvaluator
{
    /**
     * @var OrganizationalContextInterface
     */
    private $context;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidRetrievalStrategy;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * Constructor.
     *
     * @param OrganizationalContextInterface             $context              The organizational context
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy The security retrieval strategy
     * @param TokenStorageInterface                      $tokenStorage         The token storage
     */
    public function __construct(OrganizationalContextInterface $context,
                                SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy,
                                TokenStorageInterface $tokenStorage)
    {
        $this->context = $context;
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->tokenStorage = $tokenStorage;
        $this->cacheExec = array();
    }

    /**
     * Check if token has a role.
     *
     * @param string $role The role in organization
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->hasAnyRole((array) $role);
    }

    /**
     * Check if token has one of the roles.
     *
     * @param array|string $roles The roles in organization
     *
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        $roles = (array) $roles;
        $sidRoles = $this->getTokenRoles();

        if (!$sidRoles) {
            return false;
        }

        foreach ($roles as $role) {
            if (in_array($this->formatOrgRole($role), $sidRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the roles of token.
     *
     * @return string[]
     */
    protected function getTokenRoles()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return array();
        }

        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);
        $id = sha1(implode('|', $sids));

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $roles = array();

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $roles[] = $sid->getRole();
            }
        }

        return $this->cacheExec[$id] = $roles;
    }

    /**
     * Format the role with the current organization name.
     *
     * @param string $role The role
     *
     * @return string
     */
    protected function formatOrgRole($role)
    {
        if (false === strrpos($role, '__')) {
            $suffix = '';

            if (null !== $org = $this->context->getCurrentOrganization()) {
                $suffix = $org->getName();
            }

            $role .= '__'.strtoupper($suffix);
        }

        return $role;
    }
}
