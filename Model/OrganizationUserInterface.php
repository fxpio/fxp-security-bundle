<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Model;

use FOS\UserBundle\Model\GroupableInterface;
use Sonatra\Bundle\SecurityBundle\Model\Traits\RoleableInterface;

/**
 * Organization user interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationUserInterface extends RoleableInterface, GroupableInterface
{
    /**
     * Set the organization.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return self
     */
    public function setOrganization($organization);

    /**
     * Get the organization.
     *
     * @return OrganizationInterface
     */
    public function getOrganization();

    /**
     * Set the user of organization.
     *
     * @param UserInterface|null $user The user of organization
     *
     * @return self
     */
    public function setUser($user);

    /**
     * Get the user of organization.
     *
     * @return UserInterface|null
     */
    public function getUser();

    /**
     * Set the email of the guest invitation.
     *
     * @param string|null $email The email
     *
     * @return self
     */
    public function setInvitationEmail($email);

    /**
     * Get the email of the guest invitation.
     *
     * @return string|null
     */
    public function getInvitationEmail();

    /**
     * Set the token of guest invitation.
     *
     * @param string|null $token The token
     *
     * @return self
     */
    public function setInvitationToken($token);

    /**
     * Get the token of guest invitation.
     *
     * @return string|null
     */
    public function getInvitationToken();

    /**
     * Check whether it is an invitation.
     *
     * @return bool
     */
    public function isInvitation();

    /**
     * Check if the organization user is an admin (contain the ROLE_ADMIN).
     *
     * @return bool
     */
    public function isAdmin();

    /**
     * @return string
     */
    public function __toString();
}
