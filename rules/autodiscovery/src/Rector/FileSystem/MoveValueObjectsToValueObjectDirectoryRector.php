<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use PhpParser\Node\Stmt\Class_;
use Rector\Autodiscovery\Analyzer\ClassAnalyzer;
use Rector\Autodiscovery\FileMover\FileMover;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 * @wip
 *
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveValueObjectsToValueObjectDirectoryRector\MoveValueObjectsToValueObjectDirectoryRectorTest
 */
final class MoveValueObjectsToValueObjectDirectoryRector extends AbstractFileSystemRector
{
    /**
     * @var FileMover
     */
    private $fileMover;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(FileMover $fileMover, ClassAnalyzer $classAnalyzer)
    {
        $this->fileMover = $fileMover;
        $this->classAnalyzer = $classAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move value object to ValueObject namespace/directory', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// app/Exception/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
// app/ValueObject/Name.php
class Name
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if ($class === null) {
            return;
        }

        // get class
        if (! $this->classAnalyzer->isValueObjectClass($class)) {
            return;
        }

        $nodesWithFileDestinationValueObject = $this->fileMover->createMovedNodesAndFilePath(
            $smartFileInfo,
            $nodes,
            'ValueObject'
        );

        // nothing to move
        if ($nodesWithFileDestinationValueObject === null) {
            return;
        }

        $this->removeFile($smartFileInfo);
        $this->printNodesWithFileDestination($nodesWithFileDestinationValueObject);
    }
}
