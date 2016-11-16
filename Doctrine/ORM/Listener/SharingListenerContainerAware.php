<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener;

use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine ORM listener for sharing filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingListenerContainerAware extends SharingListener
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        if (null !== $this->container) {
            /* @var PermissionManagerInterface $permManager */
            $permManager = $this->container->get('sonatra_security.permission_manager');

            $this->setPermissionManager($permManager);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
