<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Doctrine\ORM\Listener;

use Fxp\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine ORM listener for sharing delete.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
    protected function init(): void
    {
        if (null !== $this->container) {
            /** @var SharingManagerInterface $sharingManager */
            $sharingManager = $this->container->get('fxp_security.sharing_manager');

            $this->setSharingManager($sharingManager);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
