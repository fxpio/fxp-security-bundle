<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonatra\Bundle\SecurityBundle\Model\OrganizationUserInterface;

/**
 * Trait of organization users in user model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
trait UserOrganizationUsers
{
    /**
     * @var Collection|null
     */
    protected $userOrganizations;

    /**
     * {@inheritdoc}
     */
    public function getUserOrganizations()
    {
        return $this->userOrganizations ?: $this->userOrganizations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserOrganizationNames()
    {
        $names = array();
        foreach ($this->getUserOrganizations() as $userOrg) {
            $names[] = $userOrg->getOrganization()->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUserOrganization($name)
    {
        return in_array($name, $this->getUserOrganizationNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addUserOrganization(OrganizationUserInterface $organizationUser)
    {
        if (!$organizationUser->getOrganization()->isUserOrganization()
            && !$this->getUserOrganizations()->contains($organizationUser)) {
            $this->getUserOrganizations()->add($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUserOrganization(OrganizationUserInterface $organizationUser)
    {
        if ($this->getUserOrganizations()->contains($organizationUser)) {
            $this->getUserOrganizations()->removeElement($organizationUser);
        }

        return $this;
    }
}
