<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclObjectFilterInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\DependencyInjection\ObjectFilterExtensionInterface;
use Sonatra\Bundle\SecurityBundle\Event\ObjectFieldViewGrantedEvent;
use Sonatra\Bundle\SecurityBundle\Event\ObjectViewGrantedEvent;
use Sonatra\Bundle\SecurityBundle\Event\PostCommitObjectFilterEvent;
use Sonatra\Bundle\SecurityBundle\Event\PreCommitObjectFilterEvent;
use Sonatra\Bundle\SecurityBundle\Event\RestoreViewGrantedEvent;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\ObjectFilterEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Acl Object Filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclObjectFilter implements AclObjectFilterInterface
{
    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @var ObjectFilterExtensionInterface
     */
    private $ofe;

    /**
     * @var AclManagerInterface
     */
    private $am;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $ac;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * If the action filtering/restoring is in a transaction, then the action
     * will be executing on the commit.
     *
     * @var bool
     */
    private $isTransactionnal = false;

    /**
     * The object list not analyzed (empty after commit).
     *
     * @var array
     */
    private $queue = array();

    /**
     * The object ids of object to filter (empty after commit).
     *
     * @var array
     */
    private $toFilter = array();

    /**
     * Constructor.
     *
     * @param ObjectFilterExtensionInterface $ofe        The object filter extension
     * @param AclManagerInterface            $am         The acl manager
     * @param AuthorizationCheckerInterface  $ac         The authorization checker
     * @param EventDispatcherInterface       $dispatcher The event dispatcher
     */
    public function __construct(ObjectFilterExtensionInterface $ofe,
                                AclManagerInterface $am,
                                AuthorizationCheckerInterface  $ac,
                                EventDispatcherInterface $dispatcher)
    {
        $this->uow = new UnitOfWork();
        $this->ofe = $ofe;
        $this->am = $am;
        $this->ac = $ac;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return $this->uow;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->isTransactionnal = true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $event = new PreCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch(ObjectFilterEvents::PRE_COMMIT, $event);

        $this->am->preloadAcls(array_values($this->queue));

        foreach ($this->queue as $id => $object) {
            if (in_array($id, $this->toFilter)) {
                $this->doFilter($object);
                continue;
            }

            $this->doRestore($object);
        }

        $event = new PostCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch(ObjectFilterEvents::POST_COMMIT, $event);

        $this->queue = array();
        $this->isTransactionnal = false;
    }

    /**
     * {@inheritdoc}
     */
    public function attach($object)
    {
        $this->uow->attach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        $this->uow->detach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->uow->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function filter($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The "object" parameter must be an object instance');
        }

        $id = spl_object_hash($object);

        $this->uow->attach($object);
        $this->queue[$id] = $object;
        $this->toFilter[] = $id;

        if (!$this->isTransactionnal) {
            $this->commit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restore($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The "object" parameter must be an object instance');
        }

        $this->uow->attach($object);
        $this->queue[spl_object_hash($object)] = $object;

        if (!$this->isTransactionnal) {
            $this->commit();
        }
    }

    /**
     * Executes the filtering.
     *
     * @param object $object
     */
    protected function doFilter($object)
    {
        $clearAll = false;
        $id = spl_object_hash($object);
        array_splice($this->toFilter, array_search($id, $this->toFilter), 1);

        if (!$this->isViewGranted($object)) {
            $clearAll = true;
        }

        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $fieldVote = new FieldVote($object, $property->getName());
            $value = $property->getValue($object);

            if (null !== $value && ($clearAll || !$this->isViewGranted($fieldVote))) {
                $value = $this->ofe->filterValue($value);
                $property->setValue($object, $value);
            }
        }
    }

    /**
     * Executes the restoring.
     *
     * @param object $object
     */
    protected function doRestore($object)
    {
        $changeSet = $this->uow->getObjectChangeSet($object);
        $ref = new \ReflectionClass($object);

        foreach ($changeSet as $field => $values) {
            $fv = new FieldVote($object, $field);

            if ($this->isRestoreViewGranted($fv, $values)) {
                $property = $ref->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($object, $values['old']);
            }
        }
    }

    /**
     * Check if the field value must be restored.
     *
     * @param FieldVote $fieldVote The field vote
     * @param array     $values    The map of old and new values
     *
     * @return bool
     */
    protected function isRestoreViewGranted(FieldVote $fieldVote, array $values)
    {
        $event = new RestoreViewGrantedEvent($fieldVote, $values['old'], $values['new']);
        $this->dispatcher->dispatch(ObjectFilterEvents::RESTORE_VIEW_GRANTED, $event);

        if ($event->isSkipAuthorizationChecker()) {
            return !$event->isGranted();
        }

        return !$this->ac->isGranted(BasicPermissionMap::PERMISSION_VIEW, $fieldVote)
            || (null === $values['old'] && null !== $values['new']
                && !$this->ac->isGranted(BasicPermissionMap::PERMISSION_CREATE, $fieldVote)
                && !$this->ac->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fieldVote))
            || (null !== $values['old'] && null !== $values['new']
                && !$this->ac->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fieldVote))
            || (null !== $values['old'] && null === $values['new']
                && !$this->ac->isGranted(BasicPermissionMap::PERMISSION_DELETE, $fieldVote)
                && !$this->ac->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fieldVote));
    }

    /**
     * Check if the object or object field can be seen.
     *
     * @param object|FieldVote $object The object or field vote
     *
     * @return bool
     */
    protected function isViewGranted($object)
    {
        if ($object instanceof FieldVote) {
            $eventName = ObjectFilterEvents::OBJECT_FIELD_VIEW_GRANTED;
            $event = new ObjectFieldViewGrantedEvent($object);
        } else {
            $eventName = ObjectFilterEvents::OBJECT_VIEW_GRANTED;
            $event = new ObjectViewGrantedEvent($object);
        }

        $this->dispatcher->dispatch($eventName, $event);

        if ($event->isSkipAuthorizationChecker()) {
            return $event->isGranted();
        }

        return $this->ac->isGranted(BasicPermissionMap::PERMISSION_VIEW, $object);
    }
}
