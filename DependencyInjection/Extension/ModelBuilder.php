<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Extension;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ModelBuilder implements ExtensionBuilderInterface
{
    /**
     * @var string
     */
    private $alias;

    /**
     * Constructor.
     *
     * @param string $alias The security extension alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if ('custom' !== $config['db_driver']) {
            $container->setParameter($this->alias.'.backend_type_'.$config['db_driver'], true);
        }
    }
}
