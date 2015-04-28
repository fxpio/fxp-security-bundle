<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Organizational;

use Sonatra\Bundle\SecurityBundle\Model\OrganizationInterface;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationUserInterface;

/**
 * Organizational Context interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationalContextInterface
{
    /**
     * Set the current used organization.
     *
     * @param OrganizationInterface|false|null $organization The current organization
     */
    public function setCurrentOrganization($organization);

    /**
     * Get the current used organization.
     *
     * @return OrganizationInterface|null
     */
    public function getCurrentOrganization();

    /**
     * Set the current used organization user.
     *
     * @param OrganizationUserInterface|null $organizationUser The current organization user
     */
    public function setCurrentOrganizationUser($organizationUser);

    /**
     * Get the current used organization user.
     *
     * @return OrganizationUserInterface|null
     */
    public function getCurrentOrganizationUser();
}
