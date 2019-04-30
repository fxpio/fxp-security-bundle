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

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ObjectFilterPass;
use Fxp\Component\Security\ObjectFilter\MixedValue;
use Fxp\Component\Security\ObjectFilter\ObjectFilterExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Object Filter Pass tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class ObjectFilterPassTest extends TestCase
{
    public function testProcessWithoutExtension(): void
    {
        $container = new ContainerBuilder();
        $compiler = new ObjectFilterPass();

        $this->assertCount(1, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(1, $container->getDefinitions());
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $compiler = new ObjectFilterPass();

        $def = new Definition(ObjectFilterExtension::class);
        $def->setArguments([
            [],
        ]);
        $def->setProperty('container', $container);
        $container->setDefinition('fxp_security.object_filter.extension', $def);

        $defVoter = new Definition(MixedValue::class);
        $defVoter->addTag('fxp_security.object_filter.voter');
        $container->setDefinition('fxp_security.object_filter.voter.mixed', $defVoter);

        $compiler->process($container);
        $this->assertCount(3, $container->getDefinitions());
        $this->assertCount(1, $def->getArgument(0));
    }
}
