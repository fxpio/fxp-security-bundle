<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\AclRuleDefinitionPass;
use Sonatra\Bundle\SecurityBundle\Factory\HostRoleFactory;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HostRoleFactory());

        $container->addCompilerPass(new AclRuleDefinitionPass());
    }
}
