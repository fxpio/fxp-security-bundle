<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection;

use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\AnnotationBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\ExpressionLanguageBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\ExtensionBuilderInterface;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\HostRoleBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\ModelBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\ObjectFilterBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\OrganizationalContextBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\PermissionBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\RoleHierarchyBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\SecurityIdentityBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\SecurityVoterBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\SharingBuilder;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension\ValidatorBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The extension that fulfills the infos for the container from configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $ref = new \ReflectionClass($this);
        $configPath = dirname(dirname($ref->getFileName())).'/Resources/config';
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
        return array(
            new ModelBuilder($this->getAlias()),
            new SecurityIdentityBuilder(),
            new PermissionBuilder(),
            new ObjectFilterBuilder(),
            new HostRoleBuilder(),
            new RoleHierarchyBuilder(),
            new SecurityVoterBuilder(),
            new OrganizationalContextBuilder(),
            new ExpressionLanguageBuilder(),
            new AnnotationBuilder($this),
            new SharingBuilder(),
            new ValidatorBuilder(),
        );
    }
}
