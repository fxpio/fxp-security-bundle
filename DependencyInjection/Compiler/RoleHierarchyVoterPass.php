<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configure the role hierarchy voter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleHierarchyVoterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('fxp_security.access.role_voter')) {
            $container->removeDefinition('security.access.role_hierarchy_voter');
            $container->removeDefinition('security.access.simple_role_voter');
        }
    }
}
