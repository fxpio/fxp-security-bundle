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

use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Sonatra\Bundle\SecurityBundle\Acl\Util\AclUtils;

/**
 * Strategy to be used for retrieving object identities from domain objects.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectIdentityRetrievalStrategy implements ObjectIdentityRetrievalStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($domainObject)
    {
        if ($domainObject instanceof ObjectIdentity) {
            return $domainObject;
        }

        if ($domainObject instanceof FieldVote) {
            return $this->getObjectIdentity($domainObject->getDomainObject());
        }

        if (is_string($domainObject)) {
            return new ObjectIdentity(AclManipulator::CLASS_TYPE, AclUtils::convertDomainObjectToClassname($domainObject));
        }

        try {
            return ObjectIdentity::fromDomainObject($domainObject);

        } catch (InvalidDomainObjectException $ex) {
        }

        return new ObjectIdentity(AclManipulator::OBJECT_TYPE, AclUtils::convertDomainObjectToClassname($domainObject));
    }
}
