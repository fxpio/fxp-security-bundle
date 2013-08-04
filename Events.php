<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle;

final class Events
{
    /**
     * The PRE_REACHABLE_ROLES event occurs before the research of all
     * children roles.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\ReachableRoleEvent instance.
     *
     * @var string
     */
    const PRE_REACHABLE_ROLES = 'sonatra_security.reachable_roles.pre';

    /**
     * The POST_REACHABLE_ROLES event occurs after the research of all
     * children roles.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\ReachableRoleEvent instance.
     *
     * @var string
     */
    const POST_REACHABLE_ROLES = 'sonatra_security.reachable_roles.post';
}
