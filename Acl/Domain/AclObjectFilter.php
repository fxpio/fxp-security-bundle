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
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var SecurityContextInterface
     */
    private $sc;

    /**
     * If the action filtering/restoring is in a transaction, then the action
     * will be executing on the commit.
     *
     * @var boolean
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
     * @param ObjectFilterExtensionInterface $ofe
     * @param AclManagerInterface            $am
     * @param SecurityContextInterface       $sc
     */
    public function __construct(ObjectFilterExtensionInterface $ofe, AclManagerInterface $am, SecurityContextInterface $sc)
    {
        $this->uow = new UnitOfWork();
        $this->ofe = $ofe;
        $this->am = $am;
        $this->sc = $sc;
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
        $this->am->preloadAcls(array_values($this->queue));

        foreach ($this->queue as $id => $object) {
            if (in_array($id, $this->toFilter)) {
                $this->doFilter($object);
                continue;
            }

            $this->doRestore($object);
        }

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

        if (!$this->sc->isGranted(BasicPermissionMap::PERMISSION_VIEW, $object)) {
            $clearAll = true;
        }

        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            if (null !== $value && ($clearAll || !$this->sc->isGranted(BasicPermissionMap::PERMISSION_VIEW, new FieldVote($object, $property->getName())))) {
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

            if (!$this->sc->isGranted('VIEW', $fv)
                    || (null === $values['old'] && null !== $values['new'] && !$this->sc->isGranted(BasicPermissionMap::PERMISSION_CREATE, $fv) && !$this->sc->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fv))
                    || (null !== $values['old'] && null !== $values['new'] && !$this->sc->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fv))
                    || (null !== $values['old'] && null === $values['new'] && !$this->sc->isGranted(BasicPermissionMap::PERMISSION_DELETE, $fv) && !$this->sc->isGranted(BasicPermissionMap::PERMISSION_EDIT, $fv))) {
                $property = $ref->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($object, $values['old']);
            }
        }
    }
}
