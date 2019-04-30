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

use Fxp\Component\Security\Doctrine\ORM\Listener\ObjectFilterListener;
use Fxp\Component\Security\ObjectFilter\ObjectFilterInterface;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFilterListenerContainerAware extends ObjectFilterListener
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
            /** @var TokenStorageInterface $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');
            /** @var PermissionManagerInterface $permManager */
            $permManager = $this->container->get('fxp_security.permission_manager');
            /** @var ObjectFilterInterface $objectFilter */
            $objectFilter = $this->container->get('fxp_security.object_filter');

            $this->setTokenStorage($tokenStorage);
            $this->setPermissionManager($permManager);
            $this->setObjectFilter($objectFilter);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
