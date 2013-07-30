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
use JMS\SecurityExtraBundle\Exception\RuntimeException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HasAnyRoleFunctionCompiler implements FunctionCompilerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hasAnyRole';
    }

    /**
     * {@inheritdoc}
     */
    public function compilePreconditions(ExpressionCompiler $compiler, FunctionExpression $function)
    {
        if (0 === count($function->args)) {
            throw new RuntimeException('The function hasAnyRole() expects at least one argument, but got none.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compile(ExpressionCompiler $compiler, FunctionExpression $function)
    {
        $compiler
            ->compileInternal(new VariableExpression('any_role_evaluator'))
            ->write('->hasAnyRole(')
            ->compileInternal(new VariableExpression('token'))
            ->write(', [');

        $first = true;
        foreach ($function->args as $arg) {
            if (!$first) {
                $compiler->write(', ');
            }
            $first = false;
            $compiler->write('\'' . $arg->value . '\'');
        }

        $compiler->write('])');
    }
}
