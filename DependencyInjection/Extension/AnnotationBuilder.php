<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Extension;

use Doctrine\Common\Annotations\Reader;
use Fxp\Bundle\SecurityBundle\DependencyInjection\FxpSecurityExtension;
use Fxp\Component\Security\Permission\Loader\AnnotationLoader as PermissionAnnotationLoader;
use Fxp\Component\Security\Sharing\Loader\IdentityAnnotationLoader as SharingIdentityAnnotationLoader;
use Fxp\Component\Security\Sharing\Loader\SubjectAnnotationLoader as SharingSubjectAnnotationLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnnotationBuilder implements ExtensionBuilderInterface
{
    /**
     * @var FxpSecurityExtension
     */
    private $ext;

    /**
     * Constructor.
     *
     * @param FxpSecurityExtension $extension The security extension
     */
    public function __construct(FxpSecurityExtension $extension)
    {
        $this->ext = $extension;
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        if (interface_exists(Reader::class) && class_exists(Finder::class)) {
            $resourcesDef = $container->getDefinition('fxp_security.permission.array_resource');

            foreach ($config['annotations']['include_paths'] as $path) {
                $resourcesDef->addMethodCall('add', [$path, 'annotation']);
            }

            if ($config['annotations']['permissions']) {
                $loader->load('annotation_permission.xml');

                $this->ext->addAnnotatedClassesToCompile([
                    PermissionAnnotationLoader::class,
                ]);
            }

            if ($config['annotations']['sharing']) {
                $loader->load('annotation_sharing.xml');

                $this->ext->addAnnotatedClassesToCompile([
                    SharingIdentityAnnotationLoader::class,
                    SharingSubjectAnnotationLoader::class,
                ]);
            }
        }
    }
}
