<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Finder;

/**
 * @see \TomasVotruba\UnusedPublic\Tests\Templates\TemplateFilesFinder\TemplateFilesFinderTest
 */
final class TemplateFilesFinder
{
    /**
     * @param string[]  $directories
     * @return string[]
     */
    public static function findTemplateFilePaths(array $directories, string $suffix): array
    {
        $templateFilePaths = [];
        foreach ($directories as $directory) {
            $currentTemplateFilePathsL0 = glob($directory . '/*.' . $suffix);
            $currentTemplateFilePathsL1 = glob($directory . '/*/*.' . $suffix);
            $currentTemplateFilePathsL2 = glob($directory . '/**/*/*.' . $suffix);
            $currentTemplateFilePaths = array_merge($currentTemplateFilePathsL0 === \false ? [] : $currentTemplateFilePathsL0, $currentTemplateFilePathsL1 === \false ? [] : $currentTemplateFilePathsL1, $currentTemplateFilePathsL2 === \false ? [] : $currentTemplateFilePathsL2);
            $templateFilePaths = array_merge($templateFilePaths, $currentTemplateFilePaths);
        }
        return $templateFilePaths;
    }
}
