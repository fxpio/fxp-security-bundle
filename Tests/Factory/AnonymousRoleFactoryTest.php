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

use Fxp\Bundle\SecurityBundle\Factory\AnonymousRoleFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Anonymous Role Factory Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnonymousRoleFactoryTest extends TestCase
{
    public function testGetPosition()
    {
        $factory = new AnonymousRoleFactory();

        $this->assertSame('pre_auth', $factory->getPosition());
    }

    public function testGetKey()
    {
        $factory = new AnonymousRoleFactory();

        $this->assertSame('anonymous_role', $factory->getKey());
    }

    public function getConfiguration()
    {
        return [
            [true, 'ROLE_ANONYMOUS'],
            [false, null],
            [null, null],
            [['role' => 'ROLE_CUSTOM_ANONYMOUS'], 'ROLE_CUSTOM_ANONYMOUS'],
            [['role' => null], null],
        ];
    }

    /**
     * @dataProvider getConfiguration
     *
     * @param array|bool|null $config   The config
     * @param string|null     $expected The expected value
     */
    public function testAddConfiguration($config, $expected)
    {
        $builder = new ArrayNodeDefinition('anonymous_role');
        $factory = new AnonymousRoleFactory();

        $this->assertSame($builder, $factory->addConfiguration($builder));

        $processor = new Processor();
        $res = $processor->process($builder->getNode(), [$config]);

        $this->assertInternalType('array', $res);
        $this->assertArrayHasKey('role', $res);
        $this->assertSame($expected, $res['role']);
    }

    public function testCreate()
    {
        $container = new ContainerBuilder();
        $factory = new AnonymousRoleFactory();

        $this->assertCount(1, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', [], 'user_provider', 'default_entry_point');
        $valid = [
            'fxp_security.authentication.provider.anonymous_role.test_id',
            'fxp_security.authentication.listener.anonymous_role.test_id',
            'default_entry_point',
        ];

        $this->assertEquals($valid, $res);
        $this->assertCount(3, $container->getDefinitions());
    }
}
