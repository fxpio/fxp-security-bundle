<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection;

use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\AnnotationBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\AnonymousRoleBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\ExpressionLanguageBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\ExtensionBuilderInterface;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\HostRoleBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\ModelBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\ObjectFilterBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\OrganizationalContextBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\PermissionBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\RoleHierarchyBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\SecurityIdentityBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\SecurityVoterBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\SharingBuilder;
use Fxp\Bundle\SecurityBundle\DependencyInjection\Extension\ValidatorBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The extension that fulfills the infos for the container from configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $ref = new \ReflectionClass($this);
        $configPath = \dirname($ref->getFileName(), 2).'/Resources/config';
        $loader = new Loader\XmlFileLoader($container, new FileLocator($configPath));

        foreach ($this->getExtensionBuilders() as $extensionBuilder) {
            $extensionBuilder->build($container, $loader, $config);
        }
    }

    /**
     * Get the extension builders.
     *
     * @return ExtensionBuilderInterface[]
     */
    private function getExtensionBuilders()
    {
        return [
            new ModelBuilder($this->getAlias()),
            new SecurityIdentityBuilder(),
            new PermissionBuilder(),
            new ObjectFilterBuilder(),
            new HostRoleBuilder(),
            new AnonymousRoleBuilder(),
            new RoleHierarchyBuilder(),
            new SecurityVoterBuilder(),
            new OrganizationalContextBuilder(),
            new ExpressionLanguageBuilder(),
            new AnnotationBuilder($this),
            new SharingBuilder(),
            new ValidatorBuilder(),
        ];
    }
}
