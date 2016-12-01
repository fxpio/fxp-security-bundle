<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension;

use Sonatra\Bundle\SecurityBundle\DependencyInjection\SonatraSecurityExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AnnotationBuilder implements ExtensionBuilderInterface
{
    /**
     * @var SonatraSecurityExtension
     */
    private $ext;

    /**
     * Constructor.
     *
     * @param SonatraSecurityExtension $extension The security extension
     */
    public function __construct(SonatraSecurityExtension $extension)
    {
        $this->ext = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['annotations']['security']) {
            BuilderUtils::validate($container, 'annotations.security', 'sensio_framework_extra.view.guesser.class', 'sensio/framework-extra-bundle');
            $loader->load('annotation_security.xml');

            $this->ext->addClassesToCompile(array(
                'Sonatra\\Bundle\\SecurityBundle\\Listener\\SecurityAnnotationSubscriber',
            ));
        }
    }
}
