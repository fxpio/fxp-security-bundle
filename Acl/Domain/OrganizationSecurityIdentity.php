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

use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationUserInterface;
use Sonatra\Bundle\SecurityBundle\Model\UserInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * A SecurityIdentity implementation used for actual organization.
 *
 * For used the standard ACL Provider, the organization security identity is a
 * UserSecurityIdentity with the organization class name.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class OrganizationSecurityIdentity
{
    /**
     * Creates a organization security identity from a OrganizationInterface.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return UserSecurityIdentity
     */
    public static function fromAccount(OrganizationInterface $organization)
    {
        return new UserSecurityIdentity($organization->getName(), ClassUtils::getRealClass($organization));
    }

    /**
     * Creates a organization security identity from a TokenInterface.
     *
     * @param TokenInterface                      $token         The token
     * @param OrganizationalContextInterface|null $context       The organizational context
     * @param RoleHierarchyInterface|null         $roleHierarchy The role hierarchy
     *
     * @return UserSecurityIdentity[]
     */
    public static function fromToken(TokenInterface $token, OrganizationalContextInterface $context = null,
                                     RoleHierarchyInterface $roleHierarchy = null)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return array();
        }

        return null !== $context
            ? static::getSecurityIdentityForCurrentOrganization($context, $roleHierarchy)
            : static::getSecurityIdentityForAllOrganizations($user, $roleHierarchy);
    }

    /**
     * Get the security identities for all organizations of user.
     *
     * @param UserInterface               $user          The user
     * @param RoleHierarchyInterface|null $roleHierarchy The role hierarchy
     *
     * @return UserSecurityIdentity[]
     */
    protected static function getSecurityIdentityForAllOrganizations(UserInterface $user, $roleHierarchy = null)
    {
        $sids = array();

        foreach ($user->getUserOrganizations() as $userOrg) {
            $sids[] = self::fromAccount($userOrg->getOrganization());
            $roles = static::getOrganizationRoles($userOrg, $roleHierarchy);

            foreach ($roles as $role) {
                $sids[] = new RoleSecurityIdentity($role->getRole());
            }
        }

        return $sids;
    }

    /**
     * @param OrganizationalContextInterface $context       The organizational context
     * @param RoleHierarchyInterface|null    $roleHierarchy The role hierarchy
     *
     * @return UserSecurityIdentity[]
     */
    protected static function getSecurityIdentityForCurrentOrganization(OrganizationalContextInterface $context,
                                                                        $roleHierarchy = null)
    {
        $sids = array();

        $org = $context->getCurrentOrganization();
        if ($org) {
            $sids[] = self::fromAccount($org);
        }

        $userOrg = $context->getCurrentOrganizationUser();
        if (null !== $userOrg) {
            $roles = static::getOrganizationRoles($userOrg, $roleHierarchy);
            foreach ($roles as $role) {
                $sids[] = new RoleSecurityIdentity($role->getRole());
            }
        }

        return $sids;
    }

    /**
     * @param OrganizationUserInterface   $user
     * @param RoleHierarchyInterface|null $roleHierarchy
     *
     * @return RoleInterface[]
     */
    protected static function getOrganizationRoles(OrganizationUserInterface $user, $roleHierarchy = null)
    {
        $roles = $user->getRoles();
        $id = strtoupper($user->getOrganization()->getName());
        $size = count($roles);

        for ($i = 0; $i < $size; $i++) {
            $roles[$i] = new Role($roles[$i].'__'.$id);
        }

        if ($roleHierarchy instanceof RoleHierarchyInterface) {
            $roles = $roleHierarchy->getReachableRoles($roles);
        }

        return $roles;
    }
}
