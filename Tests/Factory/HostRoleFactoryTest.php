<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Sonatra\Bundle\SecurityBundle\Factory\HostRoleFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Host Role Factory Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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

        $this->assertCount(0, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', array(), 'user_provider', 'default_entry_point');
        $valid = array(
            'sonatra_security.authentication.provider.host_roles.test_id',
            'sonatra_security.authentication.listener.host_roles.test_id',
            'default_entry_point',
        );

        $this->assertEquals($valid, $res);
        $this->assertCount(2, $container->getDefinitions());
    }
}
