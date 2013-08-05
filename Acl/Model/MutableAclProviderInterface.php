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

use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface as BaseMutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * Mutable Acl Provider Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface MutableAclProviderInterface extends BaseMutableAclProviderInterface
{
    /**
     * Check if the domain object has already loaded.
     *
     * @param ObjectIdentityInterface $domainObject
     *
     * @return boolean
     */
    public function hasLoadedAcls(ObjectIdentityInterface $domainObject);
}
