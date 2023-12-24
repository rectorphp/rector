<?php

declare (strict_types=1);
namespace Rector\Core\Php;

use RectorPrefix202312\Nette\Utils\FileSystem;
use RectorPrefix202312\Nette\Utils\Json;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\ValueObject\PolyfillPackage;
final class PolyfillPackagesProvider
{
    /**
     * @var array<PolyfillPackage::*>
     */
    private $cachedPolyfillPackages = [];
    /**
     * @return array<PolyfillPackage::*>
     */
    public function provide() : array
    {
        // used in tests mostly
        if (SimpleParameterProvider::hasParameter(Option::POLYFILL_PACKAGES)) {
            return SimpleParameterProvider::provideArrayParameter(Option::POLYFILL_PACKAGES);
        }
        $projectComposerJson = \getcwd() . '/composer.json';
        if (!\file_exists($projectComposerJson)) {
            return [];
        }
        if ($this->cachedPolyfillPackages !== []) {
            return $this->cachedPolyfillPackages;
        }
        $composerContents = FileSystem::read($projectComposerJson);
        $composerJson = Json::decode($composerContents, Json::FORCE_ARRAY);
        $this->cachedPolyfillPackages = $this->filterPolyfillPackages($composerJson['require'] ?? []);
        return $this->cachedPolyfillPackages;
    }
    /**
     * @param array<string, string> $require
     * @return array<PolyfillPackage::*>
     */
    private function filterPolyfillPackages(array $require) : array
    {
        return \array_filter($require, static function (string $packageName) : bool {
            return \strncmp($packageName, 'symfony/polyfill-', \strlen('symfony/polyfill-')) !== 0;
        });
    }
}
