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

use Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Fxp\Bundle\SecurityBundle\Listener\SecurityAnnotationSubscriber;
use Fxp\Component\Security\Authorization\Expression\IsBasicAuthProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Expression Variable Storage Pass Test.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AddExpressionLanguageProvidersPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $compiler = new AddExpressionLanguageProvidersPass();

        $securityAnnotationDef = new Definition(SecurityAnnotationSubscriber::class);
        $container->setDefinition('fxp_security.subscriber.security_annotation', $securityAnnotationDef);

        $functionProviderDef = new Definition(IsBasicAuthProvider::class);
        $functionProviderDef->addTag('security.expression_language_provider');
        $container->setDefinition('test.function_provider', $functionProviderDef);

        $this->assertCount(0, $securityAnnotationDef->getMethodCalls());
        $compiler->process($container);
        $this->assertCount(1, $securityAnnotationDef->getMethodCalls());
    }
}
