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

use Fxp\Component\Security\PermissionEvents;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * Configure the validation service.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValidationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator.builder')) {
            return;
        }

        $xmlMappings = $this->getValidatorMappingFiles($container);

        if (count($xmlMappings) > 0) {
            $container->getDefinition('validator.builder')
                ->addMethodCall('addXmlMappings', [$xmlMappings]);
        }
    }

    /**
     * Get the validator mapping files.
     *
     * @param ContainerBuilder $container The container
     *
     * @return string[]
     */
    private function getValidatorMappingFiles(ContainerBuilder $container)
    {
        $files = [];

        $reflection = new \ReflectionClass(PermissionEvents::class);
        $dirname = dirname($reflection->getFileName());

        if (is_dir($dir = $dirname.'/Resources/config/validation')) {
            foreach (Finder::create()->files()->in($dir)->name('*.xml') as $file) {
                $files[] = realpath($file->getPathname());
            }

            $container->addResource(new DirectoryResource($dir));
        }

        return $files;
    }
}
