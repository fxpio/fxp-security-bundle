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

use Sonatra\Component\Security\Doctrine\ORM\Listener\PermissionCheckerListener;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionCheckerListenerContainerAware extends PermissionCheckerListener
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
            /* @var TokenStorageInterface $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');
            /* @var AuthorizationCheckerInterface $authChecker */
            $authChecker = $this->container->get('security.authorization_checker');
            /* @var PermissionManagerInterface $permManager */
            $permManager = $this->container->get('sonatra_security.permission_manager');

            $this->setTokenStorage($tokenStorage);
            $this->setAuthorizationChecker($authChecker);
            $this->setPermissionManager($permManager);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
