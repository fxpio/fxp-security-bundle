<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\AccessControlPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ConfigDependencyValidationPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ExpressionVariableStoragePass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ObjectFilterPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\OrganizationalPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\TranslatorPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ValidationPass;
use Fxp\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Fxp\Bundle\SecurityBundle\Factory\AnonymousRoleFactory;
use Fxp\Bundle\SecurityBundle\Factory\HostRoleFactory;
use Fxp\Component\Security\Exception\LogicException;
use Fxp\Component\Security\ReachableRoleEvents;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension as BaseSecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpSecurityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->registerSecurityExtension($container);

        $container->addCompilerPass(new ConfigDependencyValidationPass());
        $container->addCompilerPass(new ValidationPass());
        $container->addCompilerPass(new TranslatorPass());
        $container->addCompilerPass(new ExpressionVariableStoragePass());
        $container->addCompilerPass(new AddExpressionLanguageProvidersPass());
        $container->addCompilerPass(new ObjectFilterPass());
        $container->addCompilerPass(new OrganizationalPass());
        $container->addCompilerPass(new RegisterListenersPass('event_dispatcher',
            'fxp_security.event_listener', 'fxp_security.event_subscriber'),
            PassConfig::TYPE_BEFORE_REMOVING);

        $this->registerMappingsPass($container);
    }

    /**
     * Register and decorate the security extension, and inject the host role listener factory.
     *
     * @param ContainerBuilder $container The container
     */
    private function registerSecurityExtension(ContainerBuilder $container)
    {
        if (!$container->hasExtension('security')) {
            throw new LogicException('The FxpSecurityBundle must be registered after the SecurityBundle in your App Kernel');
        }

        /* @var BaseSecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HostRoleFactory());
        $extension->addSecurityListenerFactory(new AnonymousRoleFactory());

        $container->registerExtension(new SecurityExtension($extension));
        $container->addCompilerPass(new AccessControlPass());
    }

    /**
     * Register the doctrine mapping.
     *
     * @param ContainerBuilder $container The container
     */
    private function registerMappingsPass(ContainerBuilder $container)
    {
        $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';

        if (class_exists($ormCompilerClass)) {
            $ref = new \ReflectionClass(ReachableRoleEvents::class);
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createXmlMappingDriver(
                    [
                        realpath(\dirname($ref->getFileName()).'/Resources/config/doctrine/model') => 'Fxp\Component\Security\Model',
                    ],
                    [],
                    'fxp_security.backend_type_orm'
                )
            );
        }
    }
}
