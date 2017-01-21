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

use Sonatra\Bundle\SecurityBundle\Factory\AnonymousRoleFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Anonymous Role Factory Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AnonymousRoleFactoryTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array(true, 'ROLE_ANONYMOUS'),
            array(false, null),
            array(null, null),
            array(array('role' => 'ROLE_CUSTOM_ANONYMOUS'), 'ROLE_CUSTOM_ANONYMOUS'),
            array(array('role' => null), null),
        );
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
        $res = $processor->process($builder->getNode(), array($config));

        $this->assertInternalType('array', $res);
        $this->assertArrayHasKey('role', $res);
        $this->assertSame($expected, $res['role']);
    }

    public function testCreate()
    {
        $container = new ContainerBuilder();
        $factory = new AnonymousRoleFactory();

        $this->assertCount(0, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', array(), 'user_provider', 'default_entry_point');
        $valid = array(
            'sonatra_security.authentication.provider.anonymous_role.test_id',
            'sonatra_security.authentication.listener.anonymous_role.test_id',
            'default_entry_point',
        );

        $this->assertEquals($valid, $res);
        $this->assertCount(2, $container->getDefinitions());
    }
}
