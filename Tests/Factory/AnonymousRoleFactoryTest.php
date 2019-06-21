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
 *
 * @internal
 */
final class AnonymousRoleFactoryTest extends TestCase
{
    public function testGetPosition(): void
    {
        $factory = new AnonymousRoleFactory();

        static::assertSame('pre_auth', $factory->getPosition());
    }

    public function testGetKey(): void
    {
        $factory = new AnonymousRoleFactory();

        static::assertSame('anonymous_role', $factory->getKey());
    }

    public function getConfiguration(): array
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
     * @param null|array|bool $config   The config
     * @param null|string     $expected The expected value
     */
    public function testAddConfiguration($config, $expected): void
    {
        $builder = new ArrayNodeDefinition('anonymous_role');
        $factory = new AnonymousRoleFactory();

        $factory->addConfiguration($builder);
        static::assertCount(1, $builder->getChildNodeDefinitions());

        $processor = new Processor();
        $res = $processor->process($builder->getNode(), [$config]);

        $value = \is_array($res);
        static::assertTrue($value);
        static::assertArrayHasKey('role', $res);
        static::assertSame($expected, $res['role']);
    }

    public function testCreate(): void
    {
        $container = new ContainerBuilder();
        $factory = new AnonymousRoleFactory();

        static::assertCount(1, $container->getDefinitions());

        $res = $factory->create($container, 'test_id', [], 'user_provider', 'default_entry_point');
        $valid = [
            'fxp_security.authentication.provider.anonymous_role.test_id',
            'fxp_security.authentication.listener.anonymous_role.test_id',
            'default_entry_point',
        ];

        static::assertEquals($valid, $res);
        static::assertCount(3, $container->getDefinitions());
    }
}
