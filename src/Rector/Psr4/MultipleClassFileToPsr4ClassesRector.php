<?php declare(strict_types=1);

namespace Rector\Rector\Psr4;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
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

        /** @var Namespace_[] $namespaceNodes */
        $namespaceNodes = $this->betterNodeFinder->findInstanceOf($nodes, Namespace_::class);

        if ($this->shouldSkip($smartFileInfo, $nodes, $namespaceNodes)) {
            return;
        }

        $shouldDelete = true;

        foreach ($namespaceNodes as $namespaceNode) {
            $newStmtsSet = $this->removeAllOtherNamespaces($nodes, $namespaceNode);

            foreach ($newStmtsSet as $newStmt) {
                if (! $newStmt instanceof Namespace_) {
                    continue;
                }

                /** @var ClassLike[] $namespacedClassLikeNodes */
                $namespacedClassLikeNodes = $this->betterNodeFinder->findInstanceOf($newStmt->stmts, ClassLike::class);

                foreach ($namespacedClassLikeNodes as $classLikeNode) {
                    if ($classLikeNode instanceof Class_ && $classLikeNode->isAnonymous()) {
                        continue;
                    }

                    $this->removeAllClassLikesFromNamespaceNode($newStmt);
                    $newStmt->stmts[] = $classLikeNode;

                    $fileDestination = $this->createClassLikeFileDestination($classLikeNode, $smartFileInfo);

                    if ($smartFileInfo->getRealPath() === $fileDestination) {
                        $shouldDelete = false;
                    }

                    $this->printNodesToFilePath($newStmtsSet, $fileDestination);
                }
            }
        }

        if ($shouldDelete) {
            FileSystem::delete($smartFileInfo->getRealPath());
        }
    }

    /**
     * @param Node[] $nodes
     * @param Namespace_[] $namespaceNodes
     */
    private function shouldSkip(SmartFileInfo $smartFileInfo, array $nodes, array $namespaceNodes): bool
    {
        // process only namespaced file
        if ($namespaceNodes === []) {
            return true;
        }

        /** @var ClassLike[] $classLikeNodes */
        $classLikeNodes = $this->betterNodeFinder->findInstanceOf($nodes, ClassLike::class);

        $nonAnonymousClassLikeNodes = array_filter($classLikeNodes, function (ClassLike $classLikeNode): ?Identifier {
            return $classLikeNode->name;
        });

        // only process file with multiple classes || class with non PSR-4 format
        if ($nonAnonymousClassLikeNodes === []) {
            return true;
        }

        if (count($nonAnonymousClassLikeNodes) === 1) {
            $nonAnonymousClassNode = $nonAnonymousClassLikeNodes[0];
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

    private function createClassLikeFileDestination(ClassLike $classLikeNode, SmartFileInfo $smartFileInfo): string
    {
        $currentDirectory = dirname($smartFileInfo->getRealPath());

        return $currentDirectory . DIRECTORY_SEPARATOR . (string) $classLikeNode->name . '.php';
    }
}
