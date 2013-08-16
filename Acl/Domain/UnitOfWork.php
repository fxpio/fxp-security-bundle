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

use Sonatra\Bundle\SecurityBundle\Acl\Model\UnitOfWorkInterface;

/**
 * Acl Object Filter Unit Of Work.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * Map of the original object data of managed objects.
     * Keys are object ids (spl_object_hash). This is used for calculating changesets.
     *
     * @var array
     */
    private $originalObjectData = array();

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifiers()
    {
        return array_keys($this->originalObjectData);
    }

    /**
     * {@inheritdoc}
     */
    public function attach($object)
    {
        $oid = spl_object_hash($object);

        if (array_key_exists($oid, $this->originalObjectData)) {
            return;
        }

        $this->originalObjectData[$oid] = array();
        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            $this->originalObjectData[$oid][$property->getName()] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        $oid = spl_object_hash($object);

        if (!array_key_exists($oid, $this->originalObjectData)) {
            return;
        }

        unset($this->originalObjectData[$oid]);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectChangeSet($object)
    {
        $oid = spl_object_hash($object);

        if (!array_key_exists($oid, $this->originalObjectData)) {
            return array();
        }

        $changeSet = array();
        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $oldValue = $this->originalObjectData[$oid][$property->getName()];
            $newValue = $property->getValue($object);

            if ($newValue !== $oldValue) {
                $changeSet[$property->getName()] = array(
                        'old' => $oldValue,
                        'new' => $newValue
                );
            }
        }

        return $changeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->originalObjectData = array();
    }
}
