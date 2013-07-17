<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Sonatra\Bundle\SecurityBundle\Model\DoctrineGroupInterface;
use FOS\UserBundle\Model\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * GroupRole get the all roles in all group of token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupRole implements GroupRoleInterface
{
    private $registry;
    private $groupClassname;
    private $cache = array();

    /**
     * Constructor.
     *
     * @param Registry $registry
     * @param string   $groupClassname
     */
    public function __construct(Registry $registry, $groupClassname)
    {
        $this->registry = $registry;
        $this->groupClassname = $groupClassname;
    }

    /**
     * Get the all roles on user with roles associated in groups.
     *
     * @param TokenInterface $token
     *
     * @return RoleInterface[]
     */
    public function getReachableRoles(TokenInterface $token)
    {
        $roles = $token->getRoles();

        // add roles in authenticated user groups
        if ($token->getUser() instanceof UserInterface) {

            if (isset($this->cache[$token->getUser()->getUsername()])) {
                return $this->cache[$token->getUser()->getUsername()];

            } else {
                $em = $this->registry->getManagerForClass($this->groupClassname);
                $filterEnabled = true;

                // exception when filter is not enabled
                try {
                    $em->getFilters()->getFilter('acl')->disable();

                } catch (\Exception $e) {
                    $filterEnabled = false;
                }

                $groups = $token->getUser()->getGroups();
                $existingRoles = null;

                foreach ($groups as $group) {
                    if ($group instanceof DoctrineGroupInterface) {
                        if (null === $existingRoles) {
                            $existingRoles = array();

                            foreach ($roles as $role) {
                                if (is_string($role)) {
                                    $existingRoles[] = $role;
                                    continue;
                                }

                                $existingRoles[] = $role->getRole();
                            }
                        }

                        foreach ($group->getEntityRoles() as $role) {
                            if (!in_array($role->getRole(), $existingRoles)) {
                                $roles[] = new Role($role->getRole());
                                $existingRoles[] = $role->getRole();
                            }
                        }
                    }
                }

                // add to cache
                $this->cache[$token->getUser()->getUsername()] = $roles;

                if ($filterEnabled) {
                    $em->getFilters()->getFilter('acl')->enable();
                }
            }
        }

        return $roles;
    }
}
