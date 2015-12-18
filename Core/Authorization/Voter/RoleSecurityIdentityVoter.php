<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Authorization\Voter;

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * RoleSecurityIdentityVoter uses a SecurityIdentityRetrievalStrategy to
 * determine the roles granted to the user before voting.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleSecurityIdentityVoter extends RoleVoter
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidRetrievalStrategy;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     * @param string                                     $prefix
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy, $prefix = 'ROLE_')
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->cacheExec = array();

        parent::__construct($prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractRoles(TokenInterface $token)
    {
        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);
        $id = sha1(implode('|', $sids));

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $roles = array();

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $role = $sid->getRole();
                $roles[] = new Role($role);
            }
        }

        return $this->cacheExec[$id] = $roles;
    }
}
