<?php

declare (strict_types=1);
namespace Rector\PSR4\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer;
use Rector\PSR4\NodeManipulator\NamespaceManipulator;
use Rector\Symfony\Printer\NeighbourClassLikePrinter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\MultipleClassFileToPsr4ClassesRectorTest
 */
final class MultipleClassFileToPsr4ClassesRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PSR4\NodeManipulator\NamespaceManipulator
     */
    private $namespaceManipulator;
    /**
     * @readonly
     * @var \Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer
     */
    private $fileInfoDeletionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\Printer\NeighbourClassLikePrinter
     */
    private $neighbourClassLikePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(\Rector\PSR4\NodeManipulator\NamespaceManipulator $namespaceManipulator, \Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer $fileInfoDeletionAnalyzer, \Rector\Symfony\Printer\NeighbourClassLikePrinter $neighbourClassLikePrinter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->namespaceManipulator = $namespaceManipulator;
        $this->fileInfoDeletionAnalyzer = $fileInfoDeletionAnalyzer;
        $this->neighbourClassLikePrinter = $neighbourClassLikePrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change multiple classes in one file to standalone PSR-4 classes.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{
}

final class SecondException extends Exception
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// new file: "app/Exceptions/FirstException.php"
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{
}

// new file: "app/Exceptions/SecondException.php"
namespace App\Exceptions;

use Exception;

final class SecondException extends Exception
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
        return [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class];
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->hasAtLeastTwoClassLikes($node)) {
            return null;
        }
        $nodeToReturn = null;
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            $nodeToReturn = $this->refactorNamespace($node);
        }
        if ($node instanceof \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace) {
            $nodeToReturn = $this->refactorFileWithoutNamespace($node);
        }
        // 1. remove this node
        if ($nodeToReturn !== null) {
            return $nodeToReturn;
        }
        $smartFileInfo = $this->file->getSmartFileInfo();
        // 2. nothing to return - remove the file
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
        return null;
    }
    private function hasAtLeastTwoClassLikes(\PhpParser\Node $node) : bool
    {
        $nonAnonymousClassLikes = $this->findNonAnonymousClassLikes($node);
        return \count($nonAnonymousClassLikes) > 1;
    }
    private function refactorNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace) : ?\PhpParser\Node\Stmt\Namespace_
    {
        $classLikes = $this->findNonAnonymousClassLikes($namespace);
        $this->namespaceManipulator->removeClassLikes($namespace);
        $nodeToReturn = null;
        foreach ($classLikes as $classLike) {
            $newNamespace = clone $namespace;
            $newNamespace->stmts[] = $classLike;
            // 1. is the class that will be kept in original file?
            if ($this->fileInfoDeletionAnalyzer->isClassLikeAndFileInfoMatch($this->file, $classLike)) {
                $nodeToReturn = $newNamespace;
                continue;
            }
            // 2. new file
            $this->printNewNodes($classLike, $newNamespace);
        }
        return $nodeToReturn;
    }
    private function refactorFileWithoutNamespace(\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $fileWithoutNamespace) : ?\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace
    {
        $classLikes = $this->findNonAnonymousClassLikes($fileWithoutNamespace);
        $nodeToReturn = null;
        foreach ($classLikes as $classLike) {
            // 1. is the class that will be kept in original file?
            if ($this->fileInfoDeletionAnalyzer->isClassLikeAndFileInfoMatch($this->file, $classLike)) {
                $nodeToReturn = $fileWithoutNamespace;
                continue;
            }
            // 2. is new file
            $this->printNewNodes($classLike, $fileWithoutNamespace);
        }
        return $nodeToReturn;
    }
    /**
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $mainNode
     */
    private function printNewNodes(\PhpParser\Node\Stmt\ClassLike $classLike, $mainNode) : void
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $this->neighbourClassLikePrinter->printClassLike($classLike, $mainNode, $smartFileInfo, $this->file);
    }
    /**
     * @return ClassLike[]
     */
    private function findNonAnonymousClassLikes(\PhpParser\Node $node) : array
    {
        $classLikes = $this->betterNodeFinder->findInstanceOf([$node], \PhpParser\Node\Stmt\ClassLike::class);
        return \array_filter($classLikes, function (\PhpParser\Node\Stmt\ClassLike $classLike) : bool {
            if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
                return \true;
            }
            return !$classLike->isAnonymous();
        });
    }
}
