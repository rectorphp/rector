<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\FileSystem;

use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
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
        return new RectorDefinition('Rename file to respect class name', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// app/SomeClass.php
class AnotherClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// app/AnotherClass.php
class AnotherClass
{
}
CODE_SAMPLE
            ),
        ]);
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
