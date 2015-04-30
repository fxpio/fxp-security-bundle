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
 * GroupableVoter to determine the groups granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupableVoter implements VoterInterface
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidRetrievalStrategy;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     * @param string                                     $prefix
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy,
                                $prefix = 'GROUP_')
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->prefix = $prefix;
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
                if ($sid instanceof UserSecurityIdentity && $this->isValidGroup($attribute, $sid)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    protected function isValidGroup($attribute, UserSecurityIdentity $sid)
    {
        $ref = new \ReflectionClass($sid->getClass());

        return in_array('FOS\UserBundle\Model\GroupInterface', $ref->getInterfaceNames())
                && substr($attribute, strlen($this->prefix)) === $sid->getUsername();
    }
}
