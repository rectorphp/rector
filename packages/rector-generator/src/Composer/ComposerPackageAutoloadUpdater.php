<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Composer;

use Rector\RectorGenerator\ValueObject\Package;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\SmartFileSystem\Json\JsonFileSystem;

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

    public function processComposerAutoload(RectorRecipe $rectorRecipe): void
    {
        $composerJsonFilePath = getcwd() . '/composer.json';
        $composerJson = $this->jsonFileSystem->loadFilePathToJson($composerJsonFilePath);

        $package = $this->resolvePackage($rectorRecipe);

        if ($this->isPackageAlreadyLoaded($composerJson, $package)) {
            return;
        }

        // ask user
        $questionText = sprintf(
            'Should we update "composer.json" autoload with "%s" namespace?',
            $package->getSrcNamespace()
        );

        $isConfirmed = $this->symfonyStyle->confirm($questionText);
        if (! $isConfirmed) {
            return;
        }

        $srcAutoload = $rectorRecipe->isRectorRepository() ? ComposerJsonSection::AUTOLOAD : ComposerJsonSection::AUTOLOAD_DEV;
        $composerJson[$srcAutoload][self::PSR_4][$package->getSrcNamespace()] = $package->getSrcDirectory();

        $composerJson[ComposerJsonSection::AUTOLOAD_DEV][self::PSR_4][$package->getTestsNamespace()] = $package->getTestsDirectory();

        $this->jsonFileSystem->writeJsonToFilePath($composerJson, $composerJsonFilePath);

        $this->rebuildAutoload();
    }

    private function resolvePackage(RectorRecipe $rectorRecipe): Package
    {
        if (! $rectorRecipe->isRectorRepository()) {
            return new Package(
                'Utils\\Rector\\',
                'Utils\\Rector\\Tests\\',
                'utils/rector/src',
                'utils/rector/tests'
            );
        }

        return new Package(
            'Rector\\' . $rectorRecipe->getPackage() . '\\',
            'Rector\\' . $rectorRecipe->getPackage() . '\\Tests\\',
            'rules/' . $rectorRecipe->getPackageDirectory() . '/src',
            'rules/' . $rectorRecipe->getPackageDirectory() . '/tests'
        );
    }

    /**
     * @param mixed[] $composerJson
     */
    private function isPackageAlreadyLoaded(array $composerJson, Package $package): bool
    {
        foreach ([ComposerJsonSection::AUTOLOAD, ComposerJsonSection::AUTOLOAD_DEV] as $autoloadSection) {
            if (isset($composerJson[$autoloadSection][self::PSR_4][$package->getSrcNamespace()])) {
                return true;
            }
        }

        return false;
    }

    private function rebuildAutoload(): void
    {
        // note: do not use shell_exec, this is only effective solution for better DX
        shell_exec('composer dump');
    }
}
