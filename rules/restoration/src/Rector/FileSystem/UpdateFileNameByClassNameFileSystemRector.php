<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\FileSystem;

use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Restoration\Tests\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector\UpdateFileNameByClassNameFileSystemRectorTest
 */
final class UpdateFileNameByClassNameFileSystemRector extends AbstractFileSystemRector
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(BetterNodeFinder $betterNodeFinder, ClassNaming $classNaming)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classNaming = $classNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename file to respect class name');
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, ClassLike::class);
        if ($class === null) {
            return;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return;
        }

        $classShortName = $this->classNaming->getShortName($className);
        if ($classShortName === null) {
            return;
        }

        // matches
        if ($classShortName === $smartFileInfo->getBasenameWithoutSuffix()) {
            return;
        }

        // no match → rename file
        $newFileLocation = $smartFileInfo->getPath() . DIRECTORY_SEPARATOR . $classShortName . '.php';
        $this->moveFile($smartFileInfo, $newFileLocation);
    }
}
