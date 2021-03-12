<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Nette\Utils\FileSystem as NetteFileSystem;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class SkipNetteSymfonyFilesystemAlias
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem, ConsoleDiffer $consoleDiffer)
    {
        $this->filesystem = $filesystem;
    }

    public function fixComposerJson(string $composerJsonFile): void
    {
        $fileContent = NetteFileSystem::read($composerJsonFile);
        $this->filesystem->dumpFile($composerJsonFile, '...');
    }
}
