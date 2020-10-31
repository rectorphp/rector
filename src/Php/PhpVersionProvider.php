<?php

declare(strict_types=1);

namespace Rector\Core\Php;

use Nette\Utils\Json;
use Rector\Core\Configuration\Option;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileSystem;

final class PhpVersionProvider
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(ParameterProvider $parameterProvider, SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function provide(): string
    {
        /** @var string|null $phpVersionFeatures */
        $phpVersionFeatures = $this->parameterProvider->provideParameter(Option::PHP_VERSION_FEATURES);
        if ($phpVersionFeatures !== null) {
            return $phpVersionFeatures;
        }

        // for tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to up
            return '10.0';
        }

        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $this->provideProjectComposerJsonConfigPlatformPhp();
        if ($platformPhp) {
            return $platformPhp;
        }

        return PHP_VERSION;
    }

    public function isAtLeastPhpVersion(string $phpVersion): bool
    {
        return version_compare($this->provide(), $phpVersion) >= 0;
    }

    private function provideProjectComposerJsonConfigPlatformPhp(): ?string
    {
        $projectComposerJson = getcwd() . '/composer.json';
        if (! file_exists($projectComposerJson)) {
            return null;
        }

        $projectComposerContent = $this->smartFileSystem->readFile($projectComposerJson);
        $projectComposerJson = Json::decode($projectComposerContent, Json::FORCE_ARRAY);

        // Rector's composer.json
        if (isset($projectComposerJson['name']) && $projectComposerJson['name'] === 'rector/rector') {
            return null;
        }

        return $projectComposerJson['config']['platform']['php'] ?? null;
    }
}
