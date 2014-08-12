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
     * Constructor.
     *
     * @param SecurityContextInterface $securityContext
     * @param AclManagerInterface      $aclManager
     * @param AclRuleManagerInterface  $aclRuleManager
     * @param AclObjectFilterInterface $aclObjectFilter
     */
    public function __construct(SecurityContextInterface $securityContext,
        AclManagerInterface $aclManager, AclRuleManagerInterface $aclRuleManager,
        AclObjectFilterInterface $aclObjectFilter)
    {
        $this->securityContext = $securityContext;
        $this->aclManager = $aclManager;
        $this->aclRuleManager = $aclRuleManager;
        $this->aclObjectFilter = $aclObjectFilter;
    }

    /**
     * Specifies the list of listened events
     *
     * @return array
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
        $token = $this->securityContext->getToken();

        if ($this->aclManager->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $object = $args->getEntity();
        $this->aclObjectFilter->filter($object);
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
        $token = $this->securityContext->getToken();

        if ($this->aclManager->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $this->aclObjectFilter->beginTransaction();

        // check all scheduled insertions
        foreach ($uow->getScheduledEntityInsertions($uow) as $object) {
            $this->aclObjectFilter->restore($object);

            if (!$this->securityContext->isGranted(BasicPermissionMap::PERMISSION_CREATE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to create the entity');
            }
        }

        // check all scheduled updates
        foreach ($uow->getScheduledEntityUpdates($uow) as $object) {
            $this->aclObjectFilter->restore($object);

            if (!$this->securityContext->isGranted(BasicPermissionMap::PERMISSION_EDIT, $object)) {
                throw new AccessDeniedException('Insufficient privilege to update the entity');
            }
        }

        // check all scheduled deletations
        foreach ($uow->getScheduledEntityDeletions($uow) as $object) {
            if (!$this->securityContext->isGranted(BasicPermissionMap::PERMISSION_DELETE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to delete the entity');
            }
        }

        $this->aclObjectFilter->commit();
    }

    /**
     * Get the ACL Manager.
     *
     * @return AclManagerInterface
     */
    public function getAclManager()
    {
        return $this->aclManager;
    }

    /**
     * Get the ACL Rule Manager.
     *
     * @return AclRuleManagerInterface
     */
    public function getAclRuleManager()
    {
        return $this->aclRuleManager;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        $token = $this->securityContext->getToken();

        return $this->aclManager->getSecurityIdentities($token);
    }
}
