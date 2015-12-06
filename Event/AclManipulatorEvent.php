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

use Sonatra\Bundle\SecurityBundle\Acl\Model\PermissionContextInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * The acl manipulator event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclManipulatorEvent extends GenericEvent
{
    /**
     * Constructor.
     *
     * @param PermissionContextInterface $context
     */
    public function __construct(PermissionContextInterface $context)
    {
        parent::__construct($context, array());
    }

    /**
     * Get acl permission context.
     *
     * @return PermissionContextInterface
     */
    public function getPermissionContext()
    {
        return $this->subject;
    }
}
