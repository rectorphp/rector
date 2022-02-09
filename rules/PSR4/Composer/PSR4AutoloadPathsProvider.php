<?php

declare (strict_types=1);
namespace Rector\PSR4\Composer;

use RectorPrefix20220209\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use RectorPrefix20220209\Symplify\SmartFileSystem\Json\JsonFileSystem;
final class PSR4AutoloadPathsProvider
{
    /**
     * @var array<string, array<string, string>>
     */
    private $cachedComposerJsonPSR4AutoloadPaths = [];
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\Json\JsonFileSystem
     */
    private $jsonFileSystem;
    public function __construct(\RectorPrefix20220209\Symplify\SmartFileSystem\Json\JsonFileSystem $jsonFileSystem)
    {
        $this->jsonFileSystem = $jsonFileSystem;
    }
    /**
     * @return array<string, string|string[]>
     */
    public function provide() : array
    {
        if ($this->cachedComposerJsonPSR4AutoloadPaths !== []) {
            return $this->cachedComposerJsonPSR4AutoloadPaths;
        }
        $composerJson = $this->jsonFileSystem->loadFilePathToJson($this->getComposerJsonPath());
        $psr4Autoloads = \array_merge($composerJson[\RectorPrefix20220209\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection::AUTOLOAD]['psr-4'] ?? [], $composerJson[\RectorPrefix20220209\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection::AUTOLOAD_DEV]['psr-4'] ?? []);
        $this->cachedComposerJsonPSR4AutoloadPaths = $this->removeEmptyNamespaces($psr4Autoloads);
        return $this->cachedComposerJsonPSR4AutoloadPaths;
    }
    private function getComposerJsonPath() : string
    {
        // assume the project has "composer.json" in root directory
        return \getcwd() . '/composer.json';
    }
    /**
     * @param array<string, array<string, string>> $psr4Autoloads
     * @return array<string, array<string, string>>
     */
    private function removeEmptyNamespaces(array $psr4Autoloads) : array
    {
        return \array_filter($psr4Autoloads, function (string $psr4Autoload) : bool {
            return $psr4Autoload !== '';
        }, \ARRAY_FILTER_USE_KEY);
    }
}
