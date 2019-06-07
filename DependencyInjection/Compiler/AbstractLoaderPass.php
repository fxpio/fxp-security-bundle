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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Abstract loader compiler pass.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractLoaderPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $tagName;

    /**
     * Constructor.
     *
     * @param string $serviceId The service id
     * @param string $tagName   The tag name
     */
    public function __construct($serviceId, $tagName)
    {
        $this->serviceId = $serviceId;
        $this->tagName = $tagName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->serviceId)) {
            return;
        }

        $loaders = [];

        foreach ($this->findAndSortTaggedServices($this->tagName, $container) as $service) {
            $loaders[] = $service;
        }

        $container->getDefinition($this->serviceId)->replaceArgument(0, $loaders);
    }
}
