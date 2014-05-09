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

/**
 * The security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityEvent extends GenericEvent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->subject = array();
        $this->arguments = array();
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
