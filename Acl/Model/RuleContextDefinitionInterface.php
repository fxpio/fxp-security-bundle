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
interface RuleContextDefinitionInterface extends RuleContextInterface
{
    /**
     * Get object identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\ObjectIdentityInterface
     */
    public function getObjectIdentity();

    /**
     * Get the object instance. If the object instance is passed to the authorization voter,
     * in this case, this method returns the object instance, otherwise, a null value.
     *
     * @return object|null
     */
    public function getObject();

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
