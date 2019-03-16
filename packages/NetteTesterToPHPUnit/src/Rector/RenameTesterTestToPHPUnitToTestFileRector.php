<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class RenameTesterTestToPHPUnitToTestFileRector implements FileSystemRectorInterface
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename "*.phpt" file to "*Test.php" file');
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $oldRealPath = $smartFileInfo->getRealPath();
        $newRealPath = $this->createNewRealPath($oldRealPath);

        if ($newRealPath === $oldRealPath) {
            return;
        }

        // rename
        FileSystem::rename($oldRealPath, $newRealPath);

        // remove old file
        FileSystem::delete($oldRealPath);
    }

    private function createNewRealPath(string $oldRealPath): string
    {
        // file suffix
        $newRealPath = Strings::replace($oldRealPath, '#\.phpt$#', '.php');

        // Test suffix
        if (! Strings::endsWith($newRealPath, 'Test.php')) {
            $newRealPath = Strings::replace($newRealPath, '#\.php$#', 'Test.php');
        }

        return $newRealPath;
    }
}
