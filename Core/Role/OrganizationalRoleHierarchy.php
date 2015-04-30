<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role;

use Sonatra\Bundle\SecurityBundle\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Cache\Adapter\CacheInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRoleHierarchy extends RoleHierarchy
{
    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param array                               $hierarchy     An array defining the hierarchy
     * @param RegistryInterface                   $registry
     * @param string                              $roleClassname
     * @param CacheInterface                      $cache
     * @param OrganizationalContextInterface|null $context
     */
    public function __construct(array $hierarchy, RegistryInterface $registry, $roleClassname,
                                CacheInterface $cache, OrganizationalContextInterface $context = null)
    {
        parent::__construct($hierarchy, $registry, $roleClassname, $cache);
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUniqueId(array $roleNames)
    {
        $id = parent::getUniqueId($roleNames);

        if (null !== $this->context && null !== ($org = $this->context->getCurrentOrganization())) {
            $id = ($org->isUserOrganization() ? 'user' : $org->getId()).'__'.$id;
        }

        return $id;
    }
}
