<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\PersistentCollection;

/**
 * This class listens to all database activity and automatically adds constraints as acls / aces.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclDoctrineORMListener implements EventSubscriber
{
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
        if (!$args->getEntityManager()->getFilters()->isEnabled('acl')) {
            return;
        }

        $object = $args->getEntity();
        $className = get_class($object);
        $rightClass = false;
        $token = $this->container->get('security.context')->getToken();

        // when token is null (console)
        if (null === $token || $token->getUser() === 'console.') {
            return;
        }

        // Proxy class
        if ($object instanceof Proxy) {
            $refl = new \ReflectionClass($object);
            $className = $refl->getParentClass()->getName();
        }

        $sc = $this->container->get('security.context');
        $clearAll = false;

        if (!$sc->isGranted('VIEW', $object) || ($object instanceof Proxy && !$sc->isGranted('VIEW', $className))) {
            $clearAll = true;
        }

        $meta = $args->getEntityManager()->getClassMetadata($className);
        $fields = array_keys($meta->getReflectionProperties());
        $identifier = $meta->getIdentifier();

        if (is_array($identifier)) {
            $identifier = implode("", $identifier);
        }

        foreach ($fields as $field) {
            $value = $meta->getFieldValue($object, $field);

            if ($identifier !== $field
                    && null !== $value
                    && ($clearAll
                        || !$sc->isGranted('VIEW', new FieldVote($object, $field)))) {
                if ($value instanceof PersistentCollection) {
                    $value->clear();

                } else {
                    $value = null;
                }

                $meta->setFieldValue($object, $field, $value);
            }
        }
    }

    /**
     * This method is executed each time doctrine does a flush on an entitymanager.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$args->getEntityManager()->getFilters()->isEnabled('acl')) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $sc = $this->container->get('security.context');

        // disabled when token is empty (console)
        if (null === $sc->getToken()) {
            return;
        }

        $scheduledInsertions = $uow->getScheduledEntityInsertions($uow);
        $scheduledUpdates = $uow->getScheduledEntityUpdates($uow);
        $scheduledDeletations = $uow->getScheduledEntityDeletions($uow);

        // check all scheduled insertions
        foreach ($scheduledInsertions as $object) {
            $edited = $this->updateObject($em, $object, 'CREATE');

            if (!$edited && !$sc->isGranted('CREATE', $object)) {
                throw new AccessDeniedException('Insufficient privilege to create the resource');
            }
        }

        // check all scheduled updates
        foreach ($scheduledUpdates as $object) {
            $edited = $this->updateObject($em, $object, 'EDIT');

            if (!$edited && !$sc->isGranted('EDIT', $object)) {
                throw new AccessDeniedException('Insufficient privilege to update the resource');
            }
        }

        // check all scheduled deletations
        foreach ($scheduledDeletations as $object) {
            if (!$sc->isGranted('DELETE', $object)) {
                throw new AccessDeniedException('Insufficient privilege to delete the resource');
            }
        }
    }

    /**
     * Get the ACL Manager.
     *
     * @return AclManagerInterface
     */
    public function getAclManager()
    {
        return $this->container->get('sonatra.acl.manager');
    }

    /**
     * Get the ACL Rule Manager.
     *
     * @return AclRuleManagerInterface
     */
    public function getAclRuleManager()
    {
        return $this->container->get('sonatra.acl.rule.manager');
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

    /**
     * Update object field.
     *
     * @param EntityManager $em
     * @param object        $object
     * @param string        $type
     *
     * @return boolean True if 1 or more field must be edited
     */
    protected function updateObject(EntityManager $em, $object, $type = 'EDIT')
    {
        $sc = $this->container->get('security.context');
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($object);
        $uow->clearEntityChangeSet(spl_object_hash($object));
        $edited = false;

        foreach ($changeSet as $field => $values) {
            $viewGranted = true;

            if (null === $values[1] && !$sc->isGranted('VIEW', new FieldVote($object, $field))) {
                $viewGranted = false;
            }

            if ($viewGranted && $sc->isGranted($type, new FieldVote($object, $field))) {
                $uow->propertyChanged($object, $field, $values[0], $values[1]);
                $edited = true;
            }
        }

        if (!$edited && 0 === count($uow->getEntityChangeSet($object))) {
            $edited = true;
        }

        return $edited;
    }
}
