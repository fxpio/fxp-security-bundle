<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Dbal;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider as BaseMutableAclProvider;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Sonatra\Bundle\SecurityBundle\Acl\Model\MutableAclProviderInterface;

/**
 * Mutable Acl Provider.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MutableAclProvider extends BaseMutableAclProvider implements MutableAclProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function hasLoadedAcls(ObjectIdentityInterface $domainObject)
    {
        if (isset($this->loadedAcls[$domainObject->getType()][$domainObject->getIdentifier()])) {
            return true;
        }

        return false;
    }
}
