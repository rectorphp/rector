<?php

declare(strict_types=1);

namespace Rector\Compiler\Renaming;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class JetbrainsStubsRenamer
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function renamePhpStormStubs(string $buildDir): void
    {
        $directory = $buildDir . '/vendor/jetbrains/phpstorm-stubs';

        $this->renameStubFileSuffixes($directory);
        $this->renameFilesSuffixesInPhpStormStubsMapFile($directory);
    }

    private function renameStubFileSuffixes(string $directory): void
    {
        $this->symfonyStyle->section('Renaming jetbrains/phpstorm-stubs from "*.php" to "*.stub"');

        $stubFileInfos = $this->getStubFileInfos($directory);
        foreach ($stubFileInfos as $stubFileInfo) {
            $path = $stubFileInfo->getPathname();

            $filenameWithStubSuffix = dirname($path) . '/' . $stubFileInfo->getBasename('.php') . '.stub';
            FileSystem::rename($path, $filenameWithStubSuffix);

            $this->symfonyStyle->note(sprintf('Renaming "%s"', $stubFileInfo->getRealPath()));
        }
    }

    private function renameFilesSuffixesInPhpStormStubsMapFile(string $phpStormStubsDirectory): void
    {
        $stubsMapPath = $phpStormStubsDirectory . '/PhpStormStubsMap.php';

        $stubsMapContents = FileSystem::read($stubsMapPath);
        $stubsMapContents = Strings::replace($stubsMapContents, '#\.php\',#m', '.stub\',');

        FileSystem::write($stubsMapPath, $stubsMapContents);
    }

    /**
     * @return SplFileInfo[]
     */
    private function getStubFileInfos(string $phpStormStubsDirectory): array
    {
        if (! is_dir($phpStormStubsDirectory)) {
            return [];
        }

        $stubFinder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($phpStormStubsDirectory)
            ->notName('#PhpStormStubsMap\.php$#');

        return iterator_to_array($stubFinder->getIterator());
    }
}
