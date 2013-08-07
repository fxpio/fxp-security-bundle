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

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleContextInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * Class for Acl Rule Context.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclRuleContext implements AclRuleContextInterface
{
    /**
     * @var AclManagerInterface
     */
    protected $am;

    /**
     * @var AclRuleManagerInterface
     */
    protected $arm;

    /**
     * @var SecurityIdentityInterface
     */
    protected $sids;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var array
     */
    protected $authenticated;

    /**
     * @var boolean
     */
    protected $isSplited;

    /**
     * Constructor.
     *
     * @param AclManagerInterface     $am
     * @param AclRuleManagerInterface $arm
     * @param array                   $sids
     */
    public function __construct(AclManagerInterface $am,
            AclRuleManagerInterface $arm, array $sids)
    {
        $this->am = $am;
        $this->arm = $arm;
        $this->sids = $sids;
        $this->roles = array();
        $this->groups = array();
        $this->authenticated = array();
        $this->isSplited = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclManager()
    {
        return $this->am;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRuleManager()
    {
        return $this->arm;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities()
    {
        return $this->sids;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        $this->splitSids();

        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($name)
    {
        $this->splitSids();

        return in_array($name, $this->roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $this->splitSids();

        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($name)
    {
        $this->splitSids();

        return in_array($name, $this->groups);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        $this->splitSids();

        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticatedAnonymously()
    {
        $this->splitSids();

        return in_array('IS_AUTHENTICATED_ANONYMOUSLY', $this->authenticated);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticatedRemembered()
    {
        $this->splitSids();

        return in_array('IS_AUTHENTICATED_REMEMBERED', $this->authenticated);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticatedFully()
    {
        $this->splitSids();

        return in_array('IS_AUTHENTICATED_FULLY', $this->authenticated);
    }

    /**
     * Split the Sids to username, roles, groups and authenticated infos.
     */
    protected function splitSids()
    {
        if ($this->isSplited) {
            return;
        }

        foreach ($this->sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity
                    && 0 === strpos('IS_AUTHENTICATED_', $sid->getRole())) {
                $this->authenticated[] = $sid->getRole();

            } elseif ($sid instanceof RoleSecurityIdentity) {
                $this->roles[] = $sid->getRole();

            } elseif ($sid instanceof UserSecurityIdentity
                    && false !== strpos('User', $sid->getClass())) {
                $this->username = $sid->getUsername();

            } elseif ($sid instanceof UserSecurityIdentity
                    && false !== strpos('Group', $sid->getClass())) {
                $this->groups[] = $sid->getUsername();
            }
        }

        $this->isSplited = true;
    }
}
