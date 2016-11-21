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

use Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler\ExpressionVariableStoragePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Expression Variable Storage Pass Test.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVariableStoragePassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessWithoutExtension()
    {
        $container = new ContainerBuilder();
        $compiler = new ExpressionVariableStoragePass();

        $this->assertCount(0, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(0, $container->getDefinitions());
    }
}
