<?php

declare(strict_types=1);

namespace Rector\Compiler\Debug;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FileLister
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function listFilesInDirectory(string $directory): void
    {
        $finder = (new Finder())
            ->in($directory)
            ->name('*.php')
            ->files();

        /** @var SplFileInfo[] $phpFileInfos */
        $phpFileInfos = $finder->getIterator();

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->section(sprintf('Files found in "%s" directory', $directory));

        foreach ($phpFileInfos as $phpFileInfo) {
            $this->symfonyStyle->writeln($phpFileInfo->getRelativePathname());
        }

        $this->symfonyStyle->newLine();
    }
}
