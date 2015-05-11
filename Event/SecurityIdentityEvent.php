<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityEvent extends GenericEvent
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * Constructor.
     *
     * @param TokenInterface $token The token
     */
    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
        $this->subject = array();
        $this->arguments = array();
    }

    /**
     * Get the token.
     *
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set security identities.
     *
     * @param \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[] $securityIdentities
     */
    public function setSecurityIdentities(array $securityIdentities)
    {
        $this->subject = $securityIdentities;
    }

    /**
     * Get security identities.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->subject;
    }
}
