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

use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider as BaseMutableAclProvider;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
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

    /**
     * {@inheritdoc}
     */
    public function clearLoadedAcls()
    {
        $this->loadedAces = array();
        $this->loadedAcls = array();
    }

    /**
     * {@inheritdoc}
     */
    public function updateRoleSecurityIdentity(RoleSecurityIdentity $sid, $oldName)
    {
        $this->connection->executeQuery($this->getUpdateRoleSecurityIdentitySql($sid, $oldName));
    }

    /**
     * Constructs the SQL for updating a user security identity.
     *
     * @param RoleSecurityIdentity $roleId
     * @param string               $oldName
     *
     * @return string
     */
    protected function getUpdateRoleSecurityIdentitySql(RoleSecurityIdentity $roleId, $oldName)
    {
        if ($roleId->getRole() == $oldName) {
            throw new InvalidArgumentException('There are no changes.');
        }

        $oldIdentifier = $oldName;
        $newIdentifier = $roleId->getRole();

        return sprintf(
            'UPDATE %s SET identifier = %s WHERE identifier = %s AND username = %s',
            $this->options['sid_table_name'],
            $this->connection->quote($newIdentifier),
            $this->connection->quote($oldIdentifier),
            $this->connection->getDatabasePlatform()->convertBooleans(false)
        );
    }
}
