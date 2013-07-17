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
use Symfony\Component\Security\Core\User\UserInterface;

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
    protected $securityIdentities;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param AclManagerInterface     $am
     * @param AclRuleManagerInterface $arm
     * @param array                   $securityIdentities
     */
    public function __construct(AclManagerInterface $am,
            AclRuleManagerInterface $arm, array $securityIdentities)
    {
        $this->am = $am;
        $this->arm = $arm;
        $this->securityIdentities = $securityIdentities;
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
        return $this->securityIdentities;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        if (null === $this->user) {
            foreach ($this->securityIdentities as $identity) {
                if ($identity instanceof UserInterface) {
                    $this->user = $identity;
                    break;
                }
            }
        }

        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        $user = $this->getUser();

        if ($user instanceof UserInterface) {
            return $user->getUsername();
        }

        return (string) $user;
    }
}
