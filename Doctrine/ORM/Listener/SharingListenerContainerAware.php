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
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
            /* @var SharingManagerInterface $sharingManager */
            $sharingManager = $this->container->get('sonatra_security.sharing_manager');
            /* @var EventDispatcherInterface $dispatcher */
            $dispatcher = $this->container->get('event_dispatcher');
            /* @var SecurityIdentityManagerInterface $sidManager */
            $sidManager = $this->container->get('sonatra_security.security_identity_manager');
            /* @var TokenStorageInterface $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');

            $this->setPermissionManager($permManager);
            $this->setSharingManager($sharingManager);
            $this->setEventDispatcher($dispatcher);
            $this->setSecurityIdentityManager($sidManager);
            $this->setTokenStorage($tokenStorage);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
