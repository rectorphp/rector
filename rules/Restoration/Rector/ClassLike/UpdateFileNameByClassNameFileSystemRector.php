<?php

declare (strict_types=1);
namespace Rector\Restoration\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector\UpdateFileNameByClassNameFileSystemRectorTest
 */
final class UpdateFileNameByClassNameFileSystemRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename file to respect class name', [new CodeSample(<<<'CODE_SAMPLE'
// app/SomeClass.php
class AnotherClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// app/AnotherClass.php
class AnotherClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        $classShortName = $this->nodeNameResolver->getShortName($className);
        $filePath = $this->file->getFilePath();
        $basename = \pathinfo($filePath, \PATHINFO_FILENAME);
        if ($classShortName === $basename) {
            return null;
        }
        // no match → rename file
        $newFileLocation = \dirname($filePath) . \DIRECTORY_SEPARATOR . $classShortName . '.php';
        $this->removedAndAddedFilesCollector->addMovedFile($this->file, $newFileLocation);
        return null;
    }
}
