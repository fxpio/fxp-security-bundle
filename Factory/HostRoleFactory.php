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

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Factory for host role injection in existing token role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'sonatra_security.authentication.provider.host_role.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('sonatra_security.host_role.authentication.provider'))
        ;

        $listenerId = 'sonatra_security.authentication.listener.host_role.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('sonatra_security.host_role.authentication.listener'))
            ->replaceArgument(1, $config)
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

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
        $builder
            ->example(array('*.domain.*' => 'ROLE_WEBSITE', '*' => 'ROLE_PUBLIC'))
            ->prototype('scalar')->end()
        ;

        return $builder;
    }
}
