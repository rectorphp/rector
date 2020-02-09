<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Psr4;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Core\Tests\Rector\Psr4\MultipleClassFileToPsr4ClassesRector\MultipleClassFileToPsr4ClassesRectorTest
 */
final class MultipleClassFileToPsr4ClassesRector extends AbstractFileSystemRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns namespaced classes in one file to standalone PSR-4 classes.',
            [
                new CodeSample(
                    <<<'PHP'
namespace App\Exceptions;

use Exception;

final class FirstException extends Exception
{

}

final class SecondException extends Exception
{

}
PHP
                    ,
                    <<<'PHP'
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
PHP
                ),
            ]
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);
        $shouldDelete = $this->shouldDeleteFileInfo($smartFileInfo, $nodes);

        /** @var Namespace_[] $namespaceNodes */
        $namespaceNodes = $this->betterNodeFinder->findInstanceOf($nodes, Namespace_::class);

        if (count($namespaceNodes) > 0) {
            $this->processNamespaceNodes($smartFileInfo, $namespaceNodes, $nodes, $shouldDelete);
        } else {
            $this->processNodesWithoutNamespace($nodes, $smartFileInfo, $shouldDelete);
        }

        if ($shouldDelete) {
            $this->removeFile($smartFileInfo);
        }
    }

    /**
     * @param Stmt[] $nodes
     */
    private function shouldDeleteFileInfo(SmartFileInfo $smartFileInfo, array $nodes): bool
    {
        $classLikes = $this->betterNodeFinder->findClassLikes($nodes);
        foreach ($classLikes as $classLike) {
            $className = $this->getName($classLike);
            if ($className === null) {
                continue;
            }

            $classShortName = $this->getShortName($className);
            if ($smartFileInfo->getBasenameWithoutSuffix() === $classShortName) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Namespace_[] $namespaceNodes
     * @param Stmt[] $nodes
     */
    private function processNamespaceNodes(
        SmartFileInfo $smartFileInfo,
        array $namespaceNodes,
        array $nodes,
        bool $shouldDeleteFile
    ): void {
        foreach ($namespaceNodes as $namespaceNode) {
            $newStmtsSet = $this->removeAllOtherNamespaces($nodes, $namespaceNode);

            foreach ($newStmtsSet as $newStmt) {
                if (! $newStmt instanceof Namespace_) {
                    continue;
                }

                /** @var ClassLike[] $classLikes */
                $classLikes = $this->betterNodeFinder->findClassLikes($nodes);
                foreach ($classLikes as $classLikeNode) {
                    $this->refactorClassLike($smartFileInfo, $shouldDeleteFile, $newStmt, $classLikeNode, $newStmtsSet);
                }
            }
        }
    }

    /**
     * @param Stmt[] $nodes
     */
    private function processNodesWithoutNamespace(array $nodes, SmartFileInfo $smartFileInfo, bool $shouldDelete): void
    {
        // process only files with 2 classes and more
        $classes = $this->betterNodeFinder->findClassLikes($nodes);

        if (count($classes) <= 1) {
            return;
        }

        $declareNode = null;
        foreach ($nodes as $node) {
            if ($node instanceof Declare_) {
                $declareNode = $node;
            }

            if (! $node instanceof Class_ || $node->isAnonymous()) {
                continue;
            }

            $fileDestination = $this->createClassLikeFileDestination($node, $smartFileInfo);

            $nodes = $declareNode !== null ? [$declareNode, $node] : [$node];

            // has file changed?
            if ($shouldDelete) {
                $this->printNewNodesToFilePath($nodes, $fileDestination);
            } else {
                $this->printNodesToFilePath($nodes, $fileDestination);
            }
        }
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function removeAllOtherNamespaces(array $nodes, Namespace_ $namespaceNode): array
    {
        foreach ($nodes as $key => $stmt) {
            if ($stmt instanceof Namespace_ && $stmt !== $namespaceNode) {
                unset($nodes[$key]);
            }
        }

        return $nodes;
    }

    private function removeAllClassLikesFromNamespaceNode(Namespace_ $namespaceNode): void
    {
        foreach ($namespaceNode->stmts as $key => $namespaceStatement) {
            if ($namespaceStatement instanceof ClassLike) {
                unset($namespaceNode->stmts[$key]);
            }
        }
    }

    private function createClassLikeFileDestination(ClassLike $classLike, SmartFileInfo $smartFileInfo): string
    {
        $currentDirectory = dirname($smartFileInfo->getRealPath());

        return $currentDirectory . DIRECTORY_SEPARATOR . $classLike->name . '.php';
    }

    private function refactorClassLike(
        SmartFileInfo $smartFileInfo,
        bool $shouldDeleteFile,
        Namespace_ $newStmt,
        ClassLike $classLike,
        array $newStmtsSet
    ): void {
        $this->removeAllClassLikesFromNamespaceNode($newStmt);
        $newStmt->stmts[] = $classLike;

        $fileDestination = $this->createClassLikeFileDestination($classLike, $smartFileInfo);

        // has file changed?
        if ($shouldDeleteFile) {
            $this->printNewNodesToFilePath($newStmtsSet, $fileDestination);
        } else {
            $this->printNodesToFilePath($newStmtsSet, $fileDestination);
        }
    }
}
