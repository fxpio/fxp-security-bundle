<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Factory;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Factory for anonymous role injection in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnonymousRoleFactory extends AbstractRoleFactory
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'anonymous_role';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        /* @var ArrayNodeDefinition $builder */
        $builder
            ->example('ROLE_CUSTOM_ANONYMOUS')
            ->addDefaultsIfNotSet()
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return is_bool($v) || is_string($v);
                })
                ->then(function ($v) {
                    return array('role' => $this->getAnonymousRole($v));
                })
            ->end()
            ->children()
                ->scalarNode('role')->defaultNull()->end()
            ->end()
        ;

        return $builder;
    }

    private function getAnonymousRole($v)
    {
        if (true === $v) {
            $v = 'ROLE_ANONYMOUS';
        }

        return is_string($v)
            ? $v
            : null;
    }
}
