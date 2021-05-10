<?php

declare(strict_types=1);

namespace Rector\PSR4\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer;
use Rector\PSR4\NodeManipulator\NamespaceManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\MultipleClassFileToPsr4ClassesRectorTest
 */
final class MultipleClassFileToPsr4ClassesRector extends AbstractRector
{
    public function __construct(
        private NamespaceManipulator $namespaceManipulator,
        private FileInfoDeletionAnalyzer $fileInfoDeletionAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change multiple classes in one file to standalone PSR-4 classes.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{
}

final class SecondException extends Exception
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class, FileWithoutNamespace::class];
    }

    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->hasAtLeastTwoClassLikes($node)) {
            return null;
        }

        $nodeToReturn = null;
        if ($node instanceof Namespace_) {
            $nodeToReturn = $this->refactorNamespace($node);
        }

        if ($node instanceof FileWithoutNamespace) {
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

    private function hasAtLeastTwoClassLikes(Node $node): bool
    {
        $classes = $this->betterNodeFinder->findClassLikes($node);
        return count($classes) > 1;
    }

    private function refactorNamespace(Namespace_ $namespace): ?Namespace_
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes($namespace->stmts);

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

    private function refactorFileWithoutNamespace(FileWithoutNamespace $fileWithoutNamespace): ?FileWithoutNamespace
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes($fileWithoutNamespace->stmts);

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
     * @param Namespace_|FileWithoutNamespace $mainNode
     */
    private function printNewNodes(ClassLike $classLike, Node $mainNode): void
    {
        $smartFileInfo = $this->file->getSmartFileInfo();

        $declares = [];
        $declare = $this->betterNodeFinder->findFirstPreviousOfTypes($mainNode, [Declare_::class]);
        if ($declare instanceof Declare_) {
            $declares = [$declare];
        }

        if ($mainNode instanceof FileWithoutNamespace) {
            $nodesToPrint = array_merge($declares, [$classLike]);
        } else {
            $nodesToPrint = array_merge($declares, [$mainNode]);
        }

        $fileDestination = $this->createClassLikeFileDestination($classLike, $smartFileInfo);

        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);

        $addedFileWithNodes = new AddedFileWithNodes($fileDestination, $nodesToPrint);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }

    private function createClassLikeFileDestination(ClassLike $classLike, SmartFileInfo $smartFileInfo): string
    {
        $currentDirectory = dirname($smartFileInfo->getRealPath());
        return $currentDirectory . DIRECTORY_SEPARATOR . $classLike->name . '.php';
    }
}
