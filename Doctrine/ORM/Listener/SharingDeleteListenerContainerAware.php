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

use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine ORM listener for sharing delete.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingDeleteListenerContainerAware extends SharingDeleteListener
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
            /* @var SharingManagerInterface $sharingManager */
            $sharingManager = $this->container->get('sonatra_security.sharing_manager');

            $this->setSharingManager($sharingManager);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
