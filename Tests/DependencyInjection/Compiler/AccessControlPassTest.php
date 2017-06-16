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
use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\AccessControlPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\ExpressionLanguage\Tests\Fixtures\TestProvider;
use Symfony\Component\Security\Http\AccessMap;

/**
 * Access Control Pass tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AccessControlPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var AccessControlPass
     */
    protected $compiler;

    /**
     * @var array
     */
    protected $accessControl = array(
        array(
            'path' => '^/path/',
            'allow_if' => 'has_role("ROLE_ADMIN") and identity("input")',
            'requires_channel' => null,
            'host' => null,
            'ips' => array(),
            'methods' => array('GET'),
            'roles' => array(),
        ),
        array(
            'path' => '^/path/',
            'allow_if' => 'has_role("ROLE_ADMIN") and identity("input")',
            'requires_channel' => null,
            'host' => null,
            'ips' => array(),
            'methods' => array('GET'),
            'roles' => array(),
        ),
    );

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compiler = new AccessControlPass();

        $accessMapDef = new Definition(AccessMap::class);
        $accessMapDef->setPublic(false);

        $expressionDef = new Definition(TestProvider::class);
        $expressionDef->setPublic(false);
        $expressionDef->addTag('security.expression_language_provider');

        $this->container->setDefinition('security.access_map', $accessMapDef);
        $this->container->setDefinition('security.expression.custom_identity_function', $expressionDef);
    }

    public function testProcessWithoutAccessControl()
    {
        /* @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasParameter')
            ->with('sonatra_security.access_control')
            ->willReturn(false);

        $this->compiler->process($container);
    }

    public function testProcess()
    {
        $this->container->setParameter('sonatra_security.access_control', $this->accessControl);

        $this->compiler->process($this->container);

        $this->assertFalse($this->container->hasParameter('sonatra_security.access_control'));
    }
}
