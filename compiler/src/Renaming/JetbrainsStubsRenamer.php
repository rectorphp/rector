<?php

declare(strict_types=1);

namespace Rector\Compiler\Renaming;

use Nette\Utils\Strings;
use Rector\Compiler\Exception\CompilerShouldNotHappenException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class JetbrainsStubsRenamer
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SymfonyStyle $symfonyStyle, SmartFileSystem $smartFileSystem)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function renamePhpStormStubs(string $buildDir): void
    {
        $directory = $buildDir . '/vendor/jetbrains/phpstorm-stubs';
        if (! is_dir($directory)) {
            return;
        }

        $this->renameStubFileSuffixes($directory);
        $this->renameFilesSuffixesInPhpStormStubsMapFile($directory);
    }

    private function renameStubFileSuffixes(string $directory): void
    {
        $stubFileInfos = $this->getStubFileInfos($directory);
        $message = sprintf(
            'Renaming "%d" stub files from "%s"',
            count($stubFileInfos),
            'vendor/jetbrains/phpstorm-stubs'
        );
        $this->symfonyStyle->note($message);

        foreach ($stubFileInfos as $stubFileInfo) {
            $path = $stubFileInfo->getPathname();

            $filenameWithStubSuffix = dirname($path) . '/' . $stubFileInfo->getBasename('.php') . '.stub';
            $this->smartFileSystem->rename($path, $filenameWithStubSuffix);
        }
    }

    private function renameFilesSuffixesInPhpStormStubsMapFile(string $phpStormStubsDirectory): void
    {
        $stubsMapPath = $phpStormStubsDirectory . '/PhpStormStubsMap.php';

        if (! file_exists($stubsMapPath)) {
            throw new CompilerShouldNotHappenException(sprintf('File "%s" was not found', $stubsMapPath));
        }

        $stubsMapContents = $this->smartFileSystem->readFile($stubsMapPath);
        $stubsMapContents = Strings::replace($stubsMapContents, '#\.php\',#m', ".stub',");

        $this->smartFileSystem->dumpFile($stubsMapPath, $stubsMapContents);
    }

    /**
     * @return SplFileInfo[]
     */
    private function getStubFileInfos(string $phpStormStubsDirectory): array
    {
        if (! is_dir($phpStormStubsDirectory)) {
            throw new CompilerShouldNotHappenException(sprintf(
                'Directory "%s" was not found',
                $phpStormStubsDirectory
            ));
        }

        $stubFinder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($phpStormStubsDirectory)
            ->notName('#PhpStormStubsMap\.php$#');

        return iterator_to_array($stubFinder->getIterator());
    }
}
