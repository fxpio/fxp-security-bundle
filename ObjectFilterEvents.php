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
final class ObjectFilterEvents
{
    /**
     * The PRE_COMMIT event occurs before the preloading of ACLs and filtering of objects.
     *
     * @Event("Sonatra\Bundle\SecurityBundle\Event\PreCommitObjectFilterEvent")
     *
     * @var string
     */
    const PRE_COMMIT = 'sonatra_security.object_filter.pre_commit';

    /**
     * The POST_COMMIT event occurs after the filtering of objects.
     *
     * @Event("Sonatra\Bundle\SecurityBundle\Event\PostCommitObjectFilterEvent")
     *
     * @var string
     */
    const POST_COMMIT = 'sonatra_security.object_filter.post_commit';

    /**
     * The OBJECT_VIEW_GRANTED event occurs before that the object filter checks the ACL Rules
     * to defined if the user has the authorization to view the object.
     *
     * This event allow you to defined the granted value, and skip the ACL rules.
     *
     * @Event("Sonatra\Bundle\SecurityBundle\Event\ObjectViewGrantedEvent")
     *
     * @var string
     */
    const OBJECT_VIEW_GRANTED = 'sonatra_security.object_filter.object_view_granted';

    /**
     * The OBJECT_FIELD_VIEW_GRANTED event occurs before that the object filter checks the ACL Rules
     * to defined if the user has the authorization to view the field of object.
     *
     * This event allow you to defined the granted value, and skip the ACL rules.
     *
     * @Event("Sonatra\Bundle\SecurityBundle\Event\ObjectFieldViewGrantedEvent")
     *
     * @var string
     */
    const OBJECT_FIELD_VIEW_GRANTED = 'sonatra_security.object_filter.object_field_view_granted';

    /**
     * The RESTORE_VIEW_GRANTED event occurs before that the object filter checks the ACL Rules
     * to defined if the field value of object must be restored or not.
     *
     * This event allow you to defined the granted value, and skip the ACL rules.
     *
     * @Event("Sonatra\Bundle\SecurityBundle\Event\RestoreViewGrantedEvent")
     *
     * @var string
     */
    const RESTORE_VIEW_GRANTED = 'sonatra_security.object_filter.restore_view_granted';
}
