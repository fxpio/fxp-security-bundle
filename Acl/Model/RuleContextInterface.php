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
 * Acl Rule Context Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RuleContextInterface
{
    /**
     * Get security identities.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[]
     */
    public function getSecurityIdentities();

    /**
     * Get username.
     *
     * @return string|null
     */
    public function getUsername();

    /**
     * Check if the context has a role name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRole($name);

    /**
     * Get all role names in context.
     *
     * @return array
     */
    public function getRoles();

    /**
     * Check if the context has a group name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasGroup($name);

    /**
     * Get all group names in context.
     *
     * @return array
     */
    public function getGroups();

    /**
     * Check if the context has a organization name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOrganization($name);

    /**
     * Get all organization names in context.
     *
     * @return array
     */
    public function getOrganizations();

    /**
     * Check if the context is authenticated anonymously.
     *
     * @return bool
     */
    public function isAuthenticatedAnonymously();

    /**
     * Check if the context is authenticated remembered.
     *
     * @return bool
     */
    public function isAuthenticatedRemembered();

    /**
     * Check if the context is authenticated fully.
     *
     * @return bool
     */
    public function isAuthenticatedFully();
}
