<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Configure the organizational context service.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        /* @var $pb ParameterBag */
        $pb = $container->getParameterBag();
        $alias = 'fxp_security.organizational_context';
        $serviceIdName = 'fxp_security.organizational_context.service_id';

        if (!$container->hasAlias($alias) || !$container->hasParameter($serviceIdName)) {
            return;
        }

        $serviceId = $container->getParameter($serviceIdName);

        if (null !== $serviceId) {
            $serviceId = $this->getServiceId($container, $serviceId);
            $container->setAlias('fxp_security.organizational_context', new Alias($serviceId, true));
            $container->removeDefinition('fxp_security.organizational_context.default');
            $pb->remove('fxp_security.organizational_context.default.class');
        }

        $pb->remove($serviceIdName);
    }

    /**
     * Get the service id of organizational context.
     *
     * @param ContainerBuilder $container The container
     * @param string           $serviceId The service id defined in config
     *
     * @return string
     */
    protected function getServiceId(ContainerBuilder $container, $serviceId)
    {
        $definition = $container->getDefinition($serviceId);
        $interfaces = class_implements($definition->getClass());

        if (!in_array(OrganizationalContextInterface::class, $interfaces)) {
            $msg = 'The service "%s" must implement the %s';
            throw new InvalidConfigurationException(sprintf($msg, $serviceId, OrganizationalContextInterface::class));
        }

        return $serviceId;
    }
}
