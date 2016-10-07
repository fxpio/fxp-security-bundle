<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

/**
 * Acl Object Filter Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclObjectFilterInterface
{
    /**
     * Get acl unit of work.
     *
     * @return UnitOfWorkInterface
     */
    public function getUnitOfWork();

    /**
     * Begin the transaction.
     */
    public function beginTransaction();

    /**
     * Execute the transaction.
     */
    public function commit();

    /**
     * Attaches an object from the object filter management.
     *
     * @param object $object The object to attach
     */
    public function attach($object);

    /**
     * Detaches an object from the object filter management.
     *
     * @param object $object The object to detach
     */
    public function detach($object);

    /**
     * Clears the UnitOfWork.
     */
    public function flush();

    /**
     * Filtering the object fields with null value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function filter($object);

    /**
     * Restoring the object fields with old value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function restore($object);
}
