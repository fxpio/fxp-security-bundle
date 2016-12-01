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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityVoterBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ($config['security_voter']['role_security_identity']) {
            $loader->load('security_voter_role_security_identity.xml');
        }

        if ($config['security_voter']['groupable']) {
            $loader->load('security_voter_groupable.xml');
        }
    }
}
