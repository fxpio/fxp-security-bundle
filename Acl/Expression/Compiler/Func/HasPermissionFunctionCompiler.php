<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Expression\Compiler\Func;

use JMS\SecurityExtraBundle\Security\Authorization\Expression\Compiler\Func\FunctionCompilerInterface;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\ExpressionCompiler;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\Ast\FunctionExpression;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\Ast\VariableExpression;
use Sonatra\Bundle\SecurityBundle\Exception\JmsRuntimeException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HasPermissionFunctionCompiler implements FunctionCompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hasPermission';
    }

    /**
     * {@inheritdoc}
     */
    public function compilePreconditions(ExpressionCompiler $compiler, FunctionExpression $function)
    {
        if (2 !== count($function->args)) {
            throw new JmsRuntimeException(sprintf('The hasPermission() function expects exactly two arguments, but got "%s".', var_export($function->args, true)));
        }

        $compiler->verifyItem('token', 'Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function compile(ExpressionCompiler $compiler, FunctionExpression $function)
    {
        $compiler
            ->compileInternal(new VariableExpression('permission_evaluator'))
            ->write('->hasPermission(')
            ->compileInternal(new VariableExpression('token'))
            ->write(', ')
            ->compileInternal($function->args[0])
            ->write(', ')
            ->compileInternal($function->args[1])
            ->write(')')
        ;
    }
}
