<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Provider for host role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleProvider implements AuthenticationProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return false;
    }
}
