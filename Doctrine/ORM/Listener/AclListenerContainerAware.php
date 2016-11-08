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

use Sonatra\Component\Security\Acl\Model\AclManagerInterface;
use Sonatra\Component\Security\Acl\Model\AclObjectFilterInterface;
use Sonatra\Component\Security\Acl\Model\AclRuleManagerInterface;
use Sonatra\Component\Security\Doctrine\ORM\Listener\AclListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class listens to all database activity and automatically adds constraints as acls / aces.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclListenerContainerAware extends AclListener
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
            /* @var AclManagerInterface $aclManager */
            $aclManager = $this->container->get('sonatra_security.acl.manager');
            /* @var AclRuleManagerInterface $aclRuleManager */
            $aclRuleManager = $this->container->get('sonatra_security.acl.rule_manager');
            /* @var AclObjectFilterInterface $aclObjectFilter */
            $aclObjectFilter = $this->container->get('sonatra_security.acl.object_filter');

            $this->setTokenStorage($tokenStorage);
            $this->setAuthorizationChecker($authChecker);
            $this->setAclManager($aclManager);
            $this->setAclRuleManager($aclRuleManager);
            $this->setAclObjectFilter($aclObjectFilter);
            $this->initialized = true;
            $this->container = null;
        }
    }
}
