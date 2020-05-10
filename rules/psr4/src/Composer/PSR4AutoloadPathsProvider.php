<?php

declare(strict_types=1);

namespace Rector\PSR4\Composer;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

final class PSR4AutoloadPathsProvider
{
    /**
     * @var string[]
     */
    private $cachedComposerJsonPSR4AutoloadPaths = [];

    /**
     * @return string[]|string[][]
     */
    public function provide(): array
    {
        if ($this->cachedComposerJsonPSR4AutoloadPaths !== []) {
            return $this->cachedComposerJsonPSR4AutoloadPaths;
        }

        $composerJson = $this->readFileToJsonArray($this->getComposerJsonPath());
        $psr4Autoloads = array_merge(
            $composerJson['autoload']['psr-4'] ?? [],
            $composerJson['autoload-dev']['psr-4'] ?? []
        );

        $this->cachedComposerJsonPSR4AutoloadPaths = $this->removeEmptyNamespaces($psr4Autoloads);

        return $this->cachedComposerJsonPSR4AutoloadPaths;
    }

    /**
     * @return mixed[]
     */
    private function readFileToJsonArray(string $composerJson): array
    {
        $composerJsonContent = FileSystem::read($composerJson);

        return Json::decode($composerJsonContent, Json::FORCE_ARRAY);
    }

    private function getComposerJsonPath(): string
    {
        // assume the project has "composer.json" in root directory
        return getcwd() . '/composer.json';
    }

    /**
     * @param string[] $psr4Autoloads
     * @return string[]
     */
    private function removeEmptyNamespaces(array $psr4Autoloads): array
    {
        return array_filter($psr4Autoloads, function (string $psr4Autoload): bool {
            return $psr4Autoload !== '';
        }, ARRAY_FILTER_USE_KEY);
    }
}
