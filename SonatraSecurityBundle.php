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

use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\OrganizationalPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\AclRuleDefinitionPass;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\AclObjectFilterPass;
use Sonatra\Bundle\SecurityBundle\Factory\HostRoleFactory;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /* @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HostRoleFactory());

        $container->addCompilerPass(new AclRuleDefinitionPass());
        $container->addCompilerPass(new AclObjectFilterPass());
        $container->addCompilerPass(new OrganizationalPass());
        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher',
            'sonatra_security.event_listener', 'sonatra_security.event_subscriber'),
            PassConfig::TYPE_BEFORE_REMOVING);

        $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';

        if (class_exists($ormCompilerClass)) {
            $ref = new \ReflectionClass($this);
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createXmlMappingDriver(
                    array(
                        realpath(dirname($ref->getFileName()).'/Resources/config/doctrine/model') => 'Sonatra\Bundle\SecurityBundle\Model',
                    ),
                    array('fos_user.model_manager_name'),
                    'fos_user.backend_type_orm'
                )
            );
        }
    }
}
