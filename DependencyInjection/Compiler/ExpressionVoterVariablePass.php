<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Sonatra\Component\Security\Authorization\Voter\ExpressionVoter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVoterVariablePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$this->isValid($container)) {
            return;
        }

        $variables = array();
        foreach ($container->findTaggedServiceIds('sonatra_security.expression_voter.variables') as $id => $tags) {
            foreach ($tags as $attributes) {
                foreach ($attributes as $name => $value) {
                    $value = $this->buildValue($container, $id, $value);

                    if (null !== $value) {
                        $variables[$name] = $value;
                    }
                }
            }
        }

        $container->getDefinition('security.access.expression_voter.variable_storage')->replaceArgument(0, $variables);
    }

    /**
     * Check if the pass must be used.
     *
     * @param ContainerBuilder $container The container
     *
     * @return bool
     */
    private function isValid(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('security.access.expression_voter')
                || !$container->hasDefinition('security.access.expression_voter.variable_storage')) {
            return false;
        }

        $def = $container->getDefinition('security.access.expression_voter');
        $ref = new \ReflectionClass($def->getClass());

        return $ref->getName() === ExpressionVoter::class || $ref->isSubclassOf(ExpressionVoter::class);
    }

    /**
     * Build the value of the expression variables.
     *
     * @param ContainerBuilder $container The container
     * @param string           $serviceId The service id
     * @param mixed            $value     The value of expression variables
     *
     * @return mixed
     */
    private function buildValue(ContainerBuilder $container, $serviceId, $value)
    {
        if (is_string($value) && 0 === strpos($value, '@')) {
            $value = ltrim($value, '@');
            $optional = 0 === strpos($value, '?');
            $value = ltrim($value, '?');
            $hasDef = $container->hasDefinition($value);

            if (!$hasDef && !$optional) {
                throw new ServiceNotFoundException($value, $serviceId);
            }

            $value = $hasDef
                ? new Reference($value)
                : null;
        }

        return $value;
    }
}
