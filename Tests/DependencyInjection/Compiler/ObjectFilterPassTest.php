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

use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\ObjectFilterPass;
use Sonatra\Component\Security\ObjectFilter\MixedValue;
use Sonatra\Component\Security\ObjectFilter\ObjectFilterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Object Filter Pass tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessWithoutExtension()
    {
        $container = new ContainerBuilder();
        $compiler = new ObjectFilterPass();

        $this->assertCount(0, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(0, $container->getDefinitions());
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $compiler = new ObjectFilterPass();

        $def = new Definition(ObjectFilterExtension::class);
        $def->setArguments(array(
            array(),
        ));
        $def->setProperty('container', $container);
        $container->setDefinition('sonatra_security.object_filter.extension', $def);

        $defVoter = new Definition(MixedValue::class);
        $defVoter->addTag('sonatra_security.object_filter.voter');
        $container->setDefinition('sonatra_security.object_filter.voter.mixed', $defVoter);

        $compiler->process($container);
        $this->assertCount(2, $container->getDefinitions());
        $this->assertCount(1, $def->getArgument(0));
    }
}
