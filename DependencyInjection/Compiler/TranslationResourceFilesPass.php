<?php

namespace Bazinga\Bundle\JsTranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Hugo MONTEIRO <hugo.monteiro@gmail.com>
 */
class TranslationResourceFilesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        if (Kernel::VERSION_ID < 20700) {
            $translationFiles = $this->getTranslationFilesFromOlderSymfonyVersions($container);
        } else {
            $translationFiles = $this->getTranslationFiles($container);
        }

        $container->getDefinition('bazinga.jstranslation.translation_finder')->replaceArgument(0, $translationFiles);
    }

    private function getTranslationFilesFromOlderSymfonyVersions(ContainerBuilder $container)
    {
        $translationFiles = array();

        $methodCalls = $container->getDefinition('translator.default')->getMethodCalls();
        foreach($methodCalls as $methodCall) {
            if ($methodCall[0] === 'addResource') {
                $locale = $methodCall[1][2];
                $filename = $methodCall[1][1];

                if (!isset($translationFiles[$locale])) {
                    $translationFiles[$locale] = array();
                }

                $translationFiles[$locale][] = $filename;
            }
        }

        return $translationFiles;
    }

    private function getTranslationFiles(ContainerBuilder $container)
    {
        $translationFiles = array();

        $translatorOptions = $container->getDefinition('translator.default')->getArgument(3);
        if (isset($translatorOptions['resource_files'])) {
            $translationFiles = $translatorOptions['resource_files'];
        }

        return $translationFiles;
    }
}