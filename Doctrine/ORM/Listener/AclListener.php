<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Listener;

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Sonatra\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * This class listens to all database activity and automatically adds constraints as acls / aces.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclListener implements EventSubscriber
{
    /* @var ContainerInterface */
    protected $container;

    /**
     * Constructor of the class, that stocks the service container in itself.
     *
     * @param ContainerInterface $container The service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $token = $this->container->get('security.context')->getToken();

        if ($this->getAclManager()->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $object = $args->getEntity();
        $oFilter = $this->container->get('sonatra_security.acl.object_filter');
        $oFilter->filter($object);
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
        $sc = $this->container->get('security.context');
        $token = $sc->getToken();

        if ($this->getAclManager()->isDisabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $oFilter = $this->container->get('sonatra_security.acl.object_filter');
        $oFilter->beginTransaction();

        // check all scheduled insertions
        foreach ($uow->getScheduledEntityInsertions($uow) as $object) {
            $oFilter->restore($object);

            if (!$sc->isGranted(BasicPermissionMap::PERMISSION_CREATE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to create the entity');
            }
        }

        // check all scheduled updates
        foreach ($uow->getScheduledEntityUpdates($uow) as $object) {
            $oFilter->restore($object);

            if (!$sc->isGranted(BasicPermissionMap::PERMISSION_EDIT, $object)) {
                throw new AccessDeniedException('Insufficient privilege to update the entity');
            }
        }

        // check all scheduled deletations
        foreach ($uow->getScheduledEntityDeletions($uow) as $object) {
            if (!$sc->isGranted(BasicPermissionMap::PERMISSION_DELETE, $object)) {
                throw new AccessDeniedException('Insufficient privilege to delete the entity');
            }
        }

        $oFilter->commit();
    }

    /**
     * Get the ACL Manager.
     *
     * @return AclManagerInterface
     */
    public function getAclManager()
    {
        return $this->container->get('sonatra_security.acl.manager');
    }

    /**
     * Get the ACL Rule Manager.
     *
     * @return AclRuleManagerInterface
     */
    public function getAclRuleManager()
    {
        return $this->container->get('sonatra_security.acl.rule_manager');
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        $token = $this->container->get('security.context')->getToken();

        return $this->getAclManager()->getSecurityIdentities($token);
    }
}
