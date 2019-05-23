<?php declare(strict_types=1);

namespace Rector\Rector\Psr4;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class MultipleClassFileToPsr4ClassesRector extends AbstractFileSystemRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns namespaced classes in one file to standalone PSR-4 classes.',
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

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        if ($this->shouldSkip($smartFileInfo, $nodes)) {
            return;
        }

        $shouldDelete = false;

        /** @var Namespace_[] $namespaceNodes */
        $namespaceNodes = $this->betterNodeFinder->findInstanceOf($nodes, Namespace_::class);

        if (count($namespaceNodes)) {
            $shouldDelete = $this->processNamespaceNodes($smartFileInfo, $namespaceNodes, $nodes);
        } else {
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

                if ($smartFileInfo->getRealPath() !== $fileDestination) {
                    $shouldDelete = true;
                }

                // has file changed?

                if ($declareNode) {
                    $nodes = array_merge([$declareNode], [$node]);
                } else {
                    $nodes = [$node];
                }

                // has file changed?
                if ($shouldDelete) {
                    $this->printNewNodesToFilePath($nodes, $fileDestination);
                } else {
                    $this->printNodesToFilePath($nodes, $fileDestination);
                }
            }
        }

        if ($shouldDelete) {
            $this->removeFile($smartFileInfo);
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function shouldSkip(SmartFileInfo $smartFileInfo, array $nodes): bool
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes($nodes);

        $nonAnonymousClassLikes = array_filter($classLikes, function (ClassLike $classLikeNode): ?Identifier {
            return $classLikeNode->name;
        });

        // only process file with multiple classes || class with non PSR-4 format
        if ($nonAnonymousClassLikes === []) {
            return true;
        }

        if (count($nonAnonymousClassLikes) === 1) {
            $nonAnonymousClassNode = $nonAnonymousClassLikes[0];
            if ((string) $nonAnonymousClassNode->name === $smartFileInfo->getFilename()) {
                return true;
            }
        }

        return false;
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

    /**
     * @param Namespace_[] $namespaceNodes
     * @param Stmt[] $nodes
     */
    private function processNamespaceNodes(SmartFileInfo $smartFileInfo, array $namespaceNodes, array $nodes): bool
    {
        $shouldDelete = false;

        foreach ($namespaceNodes as $namespaceNode) {
            $newStmtsSet = $this->removeAllOtherNamespaces($nodes, $namespaceNode);

            foreach ($newStmtsSet as $newStmt) {
                if (! $newStmt instanceof Namespace_) {
                    continue;
                }

                /** @var ClassLike[] $classLikes */
                $classLikes = $this->betterNodeFinder->findClassLikes($nodes);

                if (count($classLikes) <= 1) {
                    continue;
                }

                foreach ($classLikes as $classLikeNode) {
                    $this->removeAllClassLikesFromNamespaceNode($newStmt);
                    $newStmt->stmts[] = $classLikeNode;

                    $fileDestination = $this->createClassLikeFileDestination($classLikeNode, $smartFileInfo);

                    if ($smartFileInfo->getRealPath() !== $fileDestination) {
                        $shouldDelete = true;
                    }

                    // has file changed?
                    if ($shouldDelete) {
                        $this->printNewNodesToFilePath($newStmtsSet, $fileDestination);
                    } else {
                        $this->printNodesToFilePath($newStmtsSet, $fileDestination);
                    }
                }
            }
        }

        return $shouldDelete;
    }
}
