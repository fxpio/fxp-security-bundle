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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class AclManipulatorEvents
{
    /**
     * The AclManipulatorEvents::GET event occurs before the getting of acls.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent instance.
     *
     * @var string
     */
    const GET = 'sonatra_security.acl_manipulator.get';

    /**
     * The AclManipulatorEvents::ADD event occurs before the adding of acls.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent instance.
     *
     * @var string
     */
    const ADD = 'sonatra_security.acl_manipulator.add';

    /**
     * The AclManipulatorEvents::SET event occurs before the setting of acls.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent instance.
     *
     * @var string
     */
    const SET = 'sonatra_security.acl_manipulator.set';

    /**
     * The AclManipulatorEvents::REVOKE event occurs before the revoking of acls.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent instance.
     *
     * @var string
     */
    const REVOKE = 'sonatra_security.acl_manipulator.revoke';

    /**
     * The AclManipulatorEvents::DELETE event occurs before the deleting of acls.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\AclManipulatorEvent instance.
     *
     * @var string
     */
    const DELETE = 'sonatra_security.acl_manipulator.delete';
}
