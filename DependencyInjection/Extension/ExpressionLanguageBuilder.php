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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ExpressionLanguageBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config): void
    {
        $loader->load('expression_variable_storage.xml');

        if ($config['expression']['override_voter']) {
            $loader->load('expression_voter.xml');
        }

        foreach ($config['expression']['functions'] as $function => $enabled) {
            if ($enabled) {
                $loader->load(sprintf('expression_function_%s.xml', $function));
            }
        }
    }
}
