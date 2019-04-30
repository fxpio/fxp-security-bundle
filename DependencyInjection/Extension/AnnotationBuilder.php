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

use Fxp\Bundle\SecurityBundle\DependencyInjection\FxpSecurityExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        $security = $config['annotations']['security'];
        if ($security['enabled']) {
            BuilderUtils::validate($container, 'annotations.security', 'sensio_framework_extra.view.guesser', 'sensio/framework-extra-bundle');
            $loader->load('annotation_security.xml');
            $container->getDefinition('fxp_security.subscriber.security_annotation')
                ->replaceArgument(3, $security['rename_arguments'] ? $security['rename_arguments_prefix'] : null)
            ;

            $this->ext->addAnnotatedClassesToCompile([
                'Fxp\\Bundle\\SecurityBundle\\Listener\\SecurityAnnotationSubscriber',
            ]);
        }
    }
}
