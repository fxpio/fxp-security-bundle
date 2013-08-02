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

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;

/**
 * AclVoter to determine the roles granted on object, object field, class, or
 * class field to the token before voting.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclVoter implements VoterInterface
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidRetrievalStrategy;

    /**
     * Constructor.
     *
     * @param AclManagerInterface                        $aclManager
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy
     */
    public function __construct(AclManagerInterface $aclManager,
            SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy)
    {
        $this->aclManager = $aclManager;
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return is_string($attribute) || is_int($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        // field
        if ($class instanceof FieldVote) {
            $domainObject = $class->getDomainObject();

            if (is_string($domainObject)
                    || $domainObject instanceof DomainObjectInterface
                    || $domainObject instanceof ObjectIdentityInterface) {
                return true;
            }

            if (is_object($domainObject)) {
                return method_exists($domainObject, 'getId');
            }
        }
        // class
        if (is_string($class)
                || $class instanceof DomainObjectInterface
                || $class instanceof ObjectIdentityInterface) {
            return true;
        }

        if (is_object($class)) {
            $ref = new \ReflectionClass($class);

            return $ref->hasMethod('getId');
        }

        return  false;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$this->supportsClass($object)) {
            return $result;
        }

        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;

            if ($this->aclManager->isGranted($sids, $object, $attribute)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $result;
    }
}
