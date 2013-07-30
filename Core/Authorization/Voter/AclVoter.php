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
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Sonatra\Bundle\SecurityBundle\Acl\Model\AclManagerInterface;

/**
 * AclVoter uses a Doctrine RoleHierarchy to determine the roles granted on
 * object, object field, class, or class field to the user before voting.
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
     * Constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
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
            if (is_string($class->getDomainObject()) || $class->getDomainObject() instanceof ObjectIdentityInterface) {
                return true;
            }

            if (is_object($class->getDomainObject())) {
                $ref = new \ReflectionClass($class->getDomainObject());

                return $ref->hasMethod('getId');
            }
        }

        // class
        if (is_string($class) || $class instanceof ObjectIdentityInterface) {
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

        $identities = $this->aclManager->getIdentities($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;

            if ($this->aclManager->isGranted($identities, $object, $attribute)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $result;
    }
}
