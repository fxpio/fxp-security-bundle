<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\OrganizationalPass;
use Sonatra\Component\Security\Organizational\OrganizationalContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Organizational Pass tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalPassTest extends TestCase
{
    public function testProcessWithoutService()
    {
        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();

        $this->assertCount(0, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(0, $container->getDefinitions());
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();
        $serviceIdName = 'sonatra_security.organizational_context.service_id';
        $serviceIdDefault = 'sonatra_security.organizational_context.default';
        $serviceId = 'test';

        $container->setParameter($serviceIdName, $serviceId);
        $container->setAlias('sonatra_security.organizational_context', $serviceId);

        $defDefault = new Definition(OrganizationalContext::class);
        $container->setDefinition($serviceIdDefault, $defDefault);

        $def = new Definition(OrganizationalContext::class);
        $container->setDefinition($serviceId, $def);

        $compiler->process($container);

        $this->assertTrue($container->hasAlias('sonatra_security.organizational_context'));
        $this->assertTrue($container->hasDefinition($serviceId));
        $this->assertFalse($container->hasDefinition($serviceIdDefault));
        $this->assertFalse($container->hasParameter($serviceIdName));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The service "test" must implement the Sonatra\Component\Security\Organizational\OrganizationalContextInterface
     */
    public function testProcessWithInvalidInterface()
    {
        $container = new ContainerBuilder();
        $compiler = new OrganizationalPass();
        $serviceIdName = 'sonatra_security.organizational_context.service_id';
        $serviceId = 'test';

        $container->setParameter($serviceIdName, $serviceId);
        $container->setAlias('sonatra_security.organizational_context', $serviceId);

        $def = new Definition(\stdClass::class);
        $container->setDefinition($serviceId, $def);

        $compiler->process($container);
    }
}
