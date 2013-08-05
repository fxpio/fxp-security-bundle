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
interface AclRuleContextInterface
{
    /**
     * Get Acl Manager.
     *
     * @return \Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface
     */
    public function getAclManager();

    /**
     * Get Acl Rule Manager.
     *
     * @return \Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface
     */
    public function getAclRuleManager();

    /**
     * Get security identities.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[]
     */
    public function getSecurityIdentities();

    /**
     * Get current user.
     *
     * @return \Symfony\Component\Security\Acl\User\UserInterface|null
     */
    public function getUser();

    /**
     * Get username.
     *
     * @return string|null
     */
    public function getUsername();
}
