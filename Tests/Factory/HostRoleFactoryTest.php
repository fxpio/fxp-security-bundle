<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\Factory;

use Fxp\Bundle\SecurityBundle\Factory\HostRoleFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Host Role Factory Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class HostRoleFactoryTest extends TestCase
{
    public function testGetPosition(): void
    {
        $factory = new HostRoleFactory();

        static::assertSame('pre_auth', $factory->getPosition());
    }

    public function testGetKey(): void
    {
        $factory = new HostRoleFactory();

        static::assertSame('host_roles', $factory->getKey());
    }

    public function testAddConfiguration(): void
    {
        $builder = new ArrayNodeDefinition('test');
        $factory = new HostRoleFactory();

        $factory->addConfiguration($builder);
        static::assertCount(1, $builder->getChildNodeDefinitions());
    }

    public function testCreate(): void
    {
        $container = new ContainerBuilder();
        $factory = new HostRoleFactory();

        static::assertCount(1, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', [], 'user_provider', 'default_entry_point');
        $valid = [
            'fxp_security.authentication.provider.host_roles.test_id',
            'fxp_security.authentication.listener.host_roles.test_id',
            'default_entry_point',
        ];

        static::assertEquals($valid, $res);
        static::assertCount(3, $container->getDefinitions());
    }
}
