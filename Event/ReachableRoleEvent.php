<?php

/**
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
 * This is a general purpose reachable role event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ReachableRoleEvent extends GenericEvent
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
     * Set reachable roles.
     *
     * @param \Symfony\Component\Security\Core\Role\RoleInterface[] $reachableRoles
     */
    public function setReachableRoles(array $reachableRoles)
    {
        $this->subject = $reachableRoles;
    }

    /**
     * Get reachable roles.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    public function geReachableRoles()
    {
        return $this->subject;
    }
}
