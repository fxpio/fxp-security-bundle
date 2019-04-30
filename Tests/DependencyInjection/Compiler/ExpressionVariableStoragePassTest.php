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

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\ExpressionVariableStoragePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Expression Variable Storage Pass Test.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ExpressionVariableStoragePassTest extends TestCase
{
    public function testProcessWithoutExtension(): void
    {
        $container = new ContainerBuilder();
        $compiler = new ExpressionVariableStoragePass();

        $this->assertCount(1, $container->getDefinitions());
        $compiler->process($container);
        $this->assertCount(1, $container->getDefinitions());
    }
}
