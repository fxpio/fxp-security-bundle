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
 * Configure the translator service.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        $translator = $container->getDefinition('translator.default');
        $xlfTranslations = $this->getTranslationFiles($container);

        if (\count($xlfTranslations) > 0) {
            $optionsArgumentIndex = \count($translator->getArguments()) - 1;
            $options = array_merge_recursive(
                $translator->getArgument($optionsArgumentIndex),
                ['resource_files' => $xlfTranslations]
            );

            $translator->replaceArgument($optionsArgumentIndex, $options);
        }
    }

    /**
     * Get the translation files by locales.
     *
     * @param ContainerBuilder $container The container
     *
     * @throws
     *
     * @return array
     */
    private function getTranslationFiles(ContainerBuilder $container): array
    {
        $reflection = new \ReflectionClass(PermissionEvents::class);
        $dirname = \dirname($reflection->getFileName());
        $files = [];

        if (is_dir($dir = $dirname.'/Resources/config/translations')) {
            $files = $this->findTranslationFiles($dir);
            $container->addResource(new DirectoryResource($dir));
        }

        return $files;
    }

    /**
     * Find the translation files.
     *
     * @param string $dir The directory of translation files
     *
     * @return array
     */
    private function findTranslationFiles(string $dir): array
    {
        $files = [];
        $finder = Finder::create()
            ->files()
            ->filter(static function (\SplFileInfo $file) {
                return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
            })
            ->in($dir)
        ;

        foreach ($finder->getIterator() as $file) {
            list(, $locale) = explode('.', $file->getBasename(), 3);
            if (!isset($files[$locale])) {
                $files[$locale] = [];
            }

            $files[$locale][] = realpath((string) $file);
        }

        return $files;
    }
}
