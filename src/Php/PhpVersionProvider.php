<?php

declare(strict_types=1);

namespace Rector\Core\Php;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class PhpVersionProvider
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function provide(): string
    {
        /** @var string|null $phpVersionFeatures */
        $phpVersionFeatures = $this->parameterProvider->provideParameter(Option::PHP_VERSION_FEATURES);
        if ($phpVersionFeatures !== null) {
            return $phpVersionFeatures;
        }

        // for tests
        if (PHPUnitEnvironment::isPHPUnitRun()) {
            return '10.0'; // so we don't have to up
        }

        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $this->provideProjectComposerJsonConfigPlatformPhp();
        if ($platformPhp) {
            return $platformPhp;
        }

        return PHP_VERSION;
    }

    public function isAtLeast(string $version): bool
    {
        return version_compare($this->provide(), $version) >= 0;
    }

    private function provideProjectComposerJsonConfigPlatformPhp(): ?string
    {
        $projectComposerJson = getcwd() . '/composer.json';
        if (! file_exists($projectComposerJson)) {
            return null;
        }

        $projectComposerContent = FileSystem::read($projectComposerJson);
        $projectComposerJson = Json::decode($projectComposerContent, Json::FORCE_ARRAY);

        // Rector's composer.json
        if (isset($projectComposerJson['name']) && $projectComposerJson['name'] === 'rector/rector') {
            return null;
        }

        return $projectComposerJson['config']['platform']['php'] ?? null;
    }
}
