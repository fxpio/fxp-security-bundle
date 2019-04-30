<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\DependencyInjection\Compiler;

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\OrganizationalPass;
use Fxp\Component\Security\Organizational\OrganizationalContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Organizational Pass tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class OrganizationalPassTest extends TestCase
{
    public function testProcessWithoutService(): void
    {
        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();

        $this->assertCount(1, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(1, $container->getDefinitions());
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();
        $serviceIdName = 'fxp_security.organizational_context.service_id';
        $serviceIdDefault = 'fxp_security.organizational_context.default';
        $serviceId = 'test';

        $container->setParameter($serviceIdName, $serviceId);
        $container->setAlias('fxp_security.organizational_context', $serviceId);

        $defDefault = new Definition(OrganizationalContext::class);
        $container->setDefinition($serviceIdDefault, $defDefault);

        $def = new Definition(OrganizationalContext::class);
        $container->setDefinition($serviceId, $def);

        $compiler->process($container);

        $this->assertTrue($container->hasAlias('fxp_security.organizational_context'));
        $this->assertTrue($container->hasDefinition($serviceId));
        $this->assertFalse($container->hasDefinition($serviceIdDefault));
        $this->assertFalse($container->hasParameter($serviceIdName));
    }

    public function testProcessWithInvalidInterface(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('The service "test" must implement the Fxp\\Component\\Security\\Organizational\\OrganizationalContextInterface');

        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();
        $serviceIdName = 'fxp_security.organizational_context.service_id';
        $serviceId = 'test';

        $container->setParameter($serviceIdName, $serviceId);
        $container->setAlias('fxp_security.organizational_context', $serviceId);

        $def = new Definition(\stdClass::class);
        $container->setDefinition($serviceId, $def);

        $compiler->process($container);
    }
}
