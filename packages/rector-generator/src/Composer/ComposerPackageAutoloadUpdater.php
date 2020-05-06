<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Composer;

use Rector\RectorGenerator\FileSystem\JsonFileSystem;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\Package;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class ComposerPackageAutoloadUpdater
{
    /**
     * @var string
     */
    private const PSR_4 = 'psr-4';

    /**
     * @var JsonFileSystem
     */
    private $jsonFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(JsonFileSystem $jsonFileSystem, SymfonyStyle $symfonyStyle)
    {
        $this->jsonFileSystem = $jsonFileSystem;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function processComposerAutoload(Configuration $configuration): void
    {
        // skip core, already autoloaded
        if ($configuration->getPackage() === 'Rector') {
            return;
        }

        $composerJsonFilePath = getcwd() . '/composer.json';
        $composerJson = $this->jsonFileSystem->loadFileToJson($composerJsonFilePath);

        $package = $this->resolvePackage($configuration);

        if ($this->isPackageAlreadyLoaded($composerJson, $package)) {
            return;
        }

        // ask user
        $isConfirmed = $this->symfonyStyle->confirm(sprintf(
            'Can we update "composer.json" autoload with "%s" namespace?%s Handle it manually otherwise',
            $package->getSrcNamespace(),
            PHP_EOL
        ));
        if (! $isConfirmed) {
            return;
        }

        $composerJson['autoload'][self::PSR_4][$package->getSrcNamespace()] = $package->getSrcDirectory();
        $composerJson['autoload-dev'][self::PSR_4][$package->getTestsNamespace()] = $package->getTestsDirectory();

        $this->jsonFileSystem->saveJsonToFile($composerJsonFilePath, $composerJson);

        $this->rebuildAutoload();
    }

    private function resolvePackage(Configuration $configuration): Package
    {
        if ($configuration->getPackage() === Package::UTILS) {
            return new Package(
                'Utils\\Rector\\',
                'Utils\\Rector\\Tests\\',
                'utils/rector/src',
                'utils/rector/tests'
            );
        }

        return new Package(
            'Rector\\' . $configuration->getPackage() . '\\',
            'Rector\\' . $configuration->getPackage() . '\\Tests\\',
            'rules/' . $configuration->getPackageDirectory() . '/src',
            'rules/' . $configuration->getPackageDirectory() . '/tests'
        );
    }

    private function isPackageAlreadyLoaded(array $composerJson, Package $package): bool
    {
        return isset($composerJson['autoload'][self::PSR_4][$package->getSrcNamespace()]);
    }

    private function rebuildAutoload(): void
    {
        $composerDumpProcess = new Process(['composer', 'dump']);
        $composerDumpProcess->run();
    }
}
