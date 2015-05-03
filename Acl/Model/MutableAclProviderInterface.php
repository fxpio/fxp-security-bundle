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

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface as BaseMutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Mutable Acl Provider Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface MutableAclProviderInterface extends BaseMutableAclProviderInterface
{
    /**
     * Check if the domain object has already loaded.
     *
     * @param ObjectIdentityInterface $domainObject
     *
     * @return bool
     */
    public function hasLoadedAcls(ObjectIdentityInterface $domainObject);

    /**
     * Clear the loaded Acls ans Aces.
     */
    public function clearLoadedAcls();

    /**
     * Updates a role security identity when the role's name changes.
     *
     * @param RoleSecurityIdentity $sid
     * @param string               $oldName
     */
    public function updateRoleSecurityIdentity(RoleSecurityIdentity $sid, $oldName);

    /**
     * Updates a user security identity when the user's username changes.
     *
     * @param UserSecurityIdentity $usid
     * @param string               $oldUsername
     */
    public function updateUserSecurityIdentity(UserSecurityIdentity $usid, $oldUsername);

    /**
     * Deletes the security identity from the database.
     * ACL entries have the CASCADE option on their foreign key so they will also get deleted.
     *
     * @param SecurityIdentityInterface $sid
     *
     * @throws \InvalidArgumentException
     */
    public function deleteSecurityIdentity(SecurityIdentityInterface $sid);
}
