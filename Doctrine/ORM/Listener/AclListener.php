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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclObjectFilterInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Sonatra\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * This class listens to all database activity and automatically adds constraints as acls / aces.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var AclManagerInterface
     */
    protected $aclManager;

    /**
     * @var AclRuleManagerInterface
     */
    protected $aclRuleManager;

    /**
     * @var AclObjectFilterInterface
     */
    protected $aclObjectFilter;

    /**
     * Specifies the list of listened events
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('postLoad', 'onFlush');
    }

    /**
     * This method is executed after every load that doctrine performs.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $token = $this->getSecurityContext()->getToken();

        if ($this->aclManager->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $object = $args->getEntity();
        $this->getAclObjectFilter()->filter($object);
    }

    /**
     * This method is executed each time doctrine does a flush on an entitymanager.
     *
     * @param OnFlushEventArgs $args
     *
     * @throws AccessDeniedException When insufficient privilege for called action
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $token = $this->getSecurityContext()->getToken();

        if ($this->aclManager->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $this->getAclObjectFilter()->beginTransaction();

        // check all scheduled insertions
        foreach ($uow->getScheduledEntityInsertions($uow) as $object) {
            $this->getAclObjectFilter()->restore($object);

            if (!$this->getSecurityContext()->isGranted(BasicPermissionMap::PERMISSION_CREATE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to create the entity');
            }
        }

        // check all scheduled updates
        foreach ($uow->getScheduledEntityUpdates($uow) as $object) {
            $this->getAclObjectFilter()->restore($object);

            if (!$this->getSecurityContext()->isGranted(BasicPermissionMap::PERMISSION_EDIT, $object)) {
                throw new AccessDeniedException('Insufficient privilege to update the entity');
            }
        }

        // check all scheduled deletations
        foreach ($uow->getScheduledEntityDeletions($uow) as $object) {
            if (!$this->getSecurityContext()->isGranted(BasicPermissionMap::PERMISSION_DELETE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to delete the entity');
            }
        }

        $this->getAclObjectFilter()->commit();
    }

    /**
     * Gets security context.
     *
     * @return SecurityContextInterface
     */
    public function getSecurityContext()
    {
        $this->init();

        return $this->securityContext;
    }

    /**
     * Get the ACL Manager.
     *
     * @return AclManagerInterface
     */
    public function getAclManager()
    {
        $this->init();

        return $this->aclManager;
    }

    /**
     * Get the ACL Rule Manager.
     *
     * @return AclRuleManagerInterface
     */
    public function getAclRuleManager()
    {
        $this->init();

        return $this->aclRuleManager;
    }

    /**
     * Get the ACL Object Filter.
     *
     * @return AclObjectFilterInterface
     */
    public function getAclObjectFilter()
    {
        $this->init();

        return $this->aclObjectFilter;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        $token = $this->getSecurityContext()->getToken();

        return $this->aclManager->getSecurityIdentities($token);
    }

    /**
     * Init listener.
     */
    private function init()
    {
        if (null !== $this->container) {
            $this->securityContext = $this->container->get('security.context');
            $this->aclManager = $this->container->get('sonatra_security.acl.manager');
            $this->aclRuleManager = $this->container->get('sonatra_security.acl.rule_manager');
            $this->aclObjectFilter = $this->container->get('sonatra_security.acl.object_filter');
            $this->container = null;
        }
    }
}
