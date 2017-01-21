<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Factory;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Factory for host role injection in existing token role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleFactory extends AbstractRoleFactory
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'host_roles';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        /* @var ArrayNodeDefinition $builder */
        $builder->example(array('*.domain.*' => 'ROLE_WEBSITE', '*' => 'ROLE_PUBLIC'));
        $builder->prototype('scalar')->end();

        return $builder;
    }
}
