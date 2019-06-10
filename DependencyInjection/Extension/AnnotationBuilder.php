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
use Fxp\Component\Security\Permission\Loader\AnnotationLoader;
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
            $loader->load('annotation.xml');

            $container->getDefinition('fxp_security.class_finder')
                ->replaceArgument(0, $config['annotations']['include_paths'])
                ->replaceArgument(1, $config['annotations']['exclude_paths'])
            ;

            if ($config['annotations']['permissions']) {
                $loader->load('annotation_permission.xml');

                $this->ext->addAnnotatedClassesToCompile([
                    AnnotationLoader::class,
                ]);
            }
        }
    }
}
