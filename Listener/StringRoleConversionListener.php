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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

/**
 * Convert the role string to Role Object (used with FosUserBundle.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class StringRoleConversionListener implements EventSubscriber
{
    protected $roleClassname;
    protected $searchRolesInClasses = array(
            'Symfony\Component\Security\Core\User\UserInterface' => 'roles',
            'FOS\UserBundle\Model\GroupInterface'                => 'roles',
    );

    /**
     * Constructor.
     *
     * @param string $roleClassname
     */
    public function __construct($roleClassname, $searchRolesInClasses = array())
    {
        $this->roleClassname = $roleClassname;

        if (count($searchRolesInClasses) > 0) {
            $this->searchRolesInClasses = $searchRolesInClasses;
        }
    }

    /**
     * Specifies the list of events to listen.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('preFlush');
    }

    /**
     * PreFlush Listener.
     *
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $map = $uow->getIdentityMap();

        // find string roles
        $roleNames = $this->findStringRoles($map);

        if (0 === count($roleNames)) {
            return;
        }

        // get roles
        $roles = $this->getRoles($em, $roleNames);

        // replace roles
        $this->replaceStringRoles($map, $roles);
    }

    /**
     * Find the string roles.
     *
     * @param array $map
     */
    protected function findStringRoles(&$map)
    {
        $roles = array();

        foreach ($map as $type => $list) {
            $ref = new \ReflectionClass($type);
            $interfaces = $ref->getInterfaceNames();

            foreach ($this->searchRolesInClasses as $classname => $field) {
                if (in_array($classname, $interfaces)) {
                    foreach ($list as $object) {
                        $roles = array_merge($roles, $this->getObjectStringRoles($object));
                    }
                }
            }
        }

        return array_keys($roles);
    }

    /**
     * Convert the role string in roles collection to Role object.
     *
     * @param Object $object
     */
    protected function getObjectStringRoles($object, $property = 'roles')
    {
        $stringRoles = array();
        $ref = new \ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $value = $prop->getValue($object);

        if ($value instanceof PersistentCollection) {
            foreach ($value as $key => $item) {
                if (is_string($item)) {
                    $stringRoles[$item] = $item;
                }
            }
        }

        $prop->setAccessible(false);

        return $stringRoles;
    }

    /**
     * Get roles.
     *
     * @param EntityManager $em
     * @param array         $roleNames
     *
     * @return array
     */
    protected function getRoles(EntityManager $em, array $roleNames)
    {
        $qb = $em->createQueryBuilder();
        $roles = $qb
            ->select('r')
            ->from($this->roleClassname, 'r')
            ->add('where', $qb->expr()->in('r.name', $roleNames))
        ->getQuery();

        $map = array();

        foreach ($roles->getResult() as $role) {
            $map[$role->getRole()] = $role;
        }

        return $map;
    }

    /**
     * Replace the string roles.
     *
     * @param array $map
     * @param array $roles Map of roles object
     */
    protected function replaceStringRoles(&$map, $roles = array())
    {
        $userInterface = 'Symfony\Component\Security\Core\User\UserInterface';
        $groupInterface = 'FOS\UserBundle\Model\GroupInterface';

        foreach ($map as $type => $list) {
            $ref = new \ReflectionClass($type);
            $interfaces = $ref->getInterfaceNames();

            foreach ($this->searchRolesInClasses as $classname => $field) {
                if (in_array($classname, $interfaces)) {
                    foreach ($list as $object) {
                        $this->replaceObjectStringRole($object, $roles);
                    }
                }
            }
        }
    }

    /**
     * Replace the string roles.
     *
     * @param mixed  $object
     * @param array  $roles    Map au roles object
     * @param string $property
     */
    protected function replaceObjectStringRole(&$object, $roles, $property = 'roles')
    {
        $ref = new \ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $value = $prop->getValue($object);

        if ($value instanceof PersistentCollection) {
            foreach ($value as $key => $item) {
                if (is_string($item)) {
                    $value->set($key, $roles[$item]);
                }
            }
        }

        $prop->setAccessible(false);
    }
}
