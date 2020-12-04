<?php

declare(strict_types=1);

namespace Rector\Core\Php;

use Nette\Utils\Json;
use Rector\Core\Configuration\Option;
use Rector\Core\Util\PhpVersionFactory;
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

    /**
     * @var PhpVersionFactory
     */
    private $phpVersionFactory;

    public function __construct(
        ParameterProvider $parameterProvider,
        SmartFileSystem $smartFileSystem,
        PhpVersionFactory $phpVersionFactory
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
        $this->phpVersionFactory = $phpVersionFactory;
    }

    public function provide(): int
    {
        /** @var int|null $phpVersionFeatures */
        $phpVersionFeatures = $this->parameterProvider->provideParameter(Option::PHP_VERSION_FEATURES);
        if ($phpVersionFeatures !== null) {
            return (int) $phpVersionFeatures;
        }

        // for tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // so we don't have to up
            return 100000;
        }

        // see https://getcomposer.org/doc/06-config.md#platform
        $platformPhp = $this->provideProjectComposerJsonConfigPlatformPhp();
        if ($platformPhp !== null) {
            return $platformPhp;
        }

        return PHP_VERSION_ID;
    }

    public function isAtLeastPhpVersion(int $phpVersion): bool
    {
        return $phpVersion <= $this->provide();
    }

    private function provideProjectComposerJsonConfigPlatformPhp(): ?int
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

        $platformPhp = $projectComposerJson['config']['platform']['php'] ?? null;
        if ($platformPhp !== null) {
            return $this->phpVersionFactory->createIntVersion($platformPhp);
        }

        return null;
    }
}
