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
 * Acl Rule Context Interface for acl rule definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AclRuleContextDefinitionInterface extends AclRuleContextInterface
{
    /**
     * Get object identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\ObjectIdentityInterface
     */
    public function getObjectIdentity();

    /**
     * Get filed name.
     *
     * @return string|null
     */
    public function getField();

    /**
     * Get masks.
     *
     * @return array
     */
    public function getMasks();
}
