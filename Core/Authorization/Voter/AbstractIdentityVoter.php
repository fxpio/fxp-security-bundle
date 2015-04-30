<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Authorization\Voter;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;

/**
 * AbstractIdentityVoter to determine the identities granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractIdentityVoter implements VoterInterface
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $sidRetrievalStrategy;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     * @param string|null                                $prefix
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy, $prefix = null)
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->prefix = null === $prefix ? $this->getDefaultPrefix() : $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        /* @var SecurityIdentityInterface[] $sids */
        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            foreach ($sids as $sid) {
                if ($sid instanceof UserSecurityIdentity && $this->isValidIdentity($attribute, $sid)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    protected function isValidIdentity($attribute, UserSecurityIdentity $sid)
    {
        $ref = new \ReflectionClass($sid->getClass());

        return in_array($this->getValidClass(), $ref->getInterfaceNames())
                && substr($attribute, strlen($this->prefix)) === $sid->getUsername();
    }

    /**
     * Get the valid class of identity.
     *
     * @return string
     */
    abstract protected function getValidClass();

    /**
     * Get the default prefix.
     *
     * @return string
     */
    abstract protected function getDefaultPrefix();
}
