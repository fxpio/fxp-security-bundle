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
 */
class HostRoleFactoryTest extends TestCase
{
    public function testGetPosition()
    {
        $factory = new HostRoleFactory();

        $this->assertSame('pre_auth', $factory->getPosition());
    }

    public function testGetKey()
    {
        $factory = new HostRoleFactory();

        $this->assertSame('host_roles', $factory->getKey());
    }

    public function testAddConfiguration()
    {
        $builder = new ArrayNodeDefinition('test');
        $factory = new HostRoleFactory();

        $this->assertSame($builder, $factory->addConfiguration($builder));
    }

    public function testCreate()
    {
        $container = new ContainerBuilder();
        $factory = new HostRoleFactory();

        $this->assertCount(1, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', [], 'user_provider', 'default_entry_point');
        $valid = [
            'fxp_security.authentication.provider.host_roles.test_id',
            'fxp_security.authentication.listener.host_roles.test_id',
            'default_entry_point',
        ];

        $this->assertEquals($valid, $res);
        $this->assertCount(3, $container->getDefinitions());
    }
}
