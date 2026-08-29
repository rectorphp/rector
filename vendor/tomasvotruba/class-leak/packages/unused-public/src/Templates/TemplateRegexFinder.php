<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Templates;

use RectorPrefix202608\TomasVotruba\UnusedPublic\Finder\TemplateFilesFinder;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Utils\Strings;
final class TemplateRegexFinder
{
    /**
     * @var array<string, string[]>
     */
    private array $resultsByCacheKey = [];
    /**
     * @param string[] $directories
     * @param string[] $innerRegexes
     * @return string[]
     */
    public function find(array $directories, string $fileSuffix, array $innerRegexes, string $targetRegex): array
    {
        $cacheKey = md5($fileSuffix . $targetRegex);
        if (isset($this->resultsByCacheKey[$cacheKey])) {
            return $this->resultsByCacheKey[$cacheKey];
        }
        $templateFilePaths = TemplateFilesFinder::findTemplateFilePaths($directories, $fileSuffix);
        // convert to file contents
        $templateFilesContents = array_map(static fn(string $templateFilePath): string => (string) file_get_contents($templateFilePath), $templateFilePaths);
        $methodCallNames = $this->matchMethodCallNames($templateFilesContents, $innerRegexes, $targetRegex);
        $this->resultsByCacheKey[$fileSuffix] = $methodCallNames;
        return $methodCallNames;
    }
    /**
     * @param string[] $templateFilesContents
     * @param string[] $innerRegexes
     * @return string[]
     */
    private function matchMethodCallNames(array $templateFilesContents, array $innerRegexes, string $targetRegex): array
    {
        $methodCallNames = [];
        foreach ($templateFilesContents as $templateFileContent) {
            foreach ($innerRegexes as $innerRegex) {
                $matches = Strings::matchAll($templateFileContent, $innerRegex);
                foreach ($matches as $match) {
                    $templateMarkupContents = $match['contents'];
                    $methodNamesMatches = Strings::matchAll($templateMarkupContents, $targetRegex);
                    foreach ($methodNamesMatches as $methodNameMatch) {
                        $methodCallNames[] = $methodNameMatch['desired_name'];
                    }
                }
            }
        }
        return array_unique($methodCallNames);
    }
}
