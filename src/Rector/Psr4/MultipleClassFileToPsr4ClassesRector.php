<?php declare(strict_types=1);

namespace Rector\Rector\Psr4;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class MultipleClassFileToPsr4ClassesRector extends AbstractFileSystemRector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

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

        foreach ($namespaceNodes as $namespaceNode) {
            $newStmtsSet = $this->removeAllOtherNamespaces($nodes, $namespaceNode);

            foreach ($newStmtsSet as $newStmt) {
                if (! $newStmt instanceof Namespace_) {
                    continue;
                }

                /** @var Class_[] $namespacedClassNodes */
                $namespacedClassNodes = $this->betterNodeFinder->findInstanceOf($newStmt->stmts, Class_::class);

                foreach ($namespacedClassNodes as $classNode) {
                    $this->removeAllClassesFromNamespaceNode($newStmt);
                    $newStmt->stmts[] = $classNode;

                    $fileDestination = $this->createClassFileDestination($classNode, $smartFileInfo);

                    $this->printNodesToFilePath($newStmtsSet, $fileDestination);
                }
            }
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

        /** @var Class_[] $classNodes */
        $classNodes = $this->betterNodeFinder->findInstanceOf($nodes, Class_::class);

        $nonAnonymousClassNodes = array_filter($classNodes, function (Class_ $classNode) {
            return $classNode->name;
        });

        // only process file with multiple classes || class with non PSR-4 format
        if ($nonAnonymousClassNodes === []) {
            return true;
        }

        if (count($nonAnonymousClassNodes) === 1) {
            $nonAnonymousClassNode = $nonAnonymousClassNodes[0];
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

    private function removeAllClassesFromNamespaceNode(Namespace_ $namespaceNode): void
    {
        foreach ($namespaceNode->stmts as $key => $namespaceStatement) {
            if ($namespaceStatement instanceof Class_) {
                unset($namespaceNode->stmts[$key]);
            }
        }
    }

    private function createClassFileDestination(Class_ $classNode, SmartFileInfo $smartFileInfo): string
    {
        $currentDirectory = dirname($smartFileInfo->getRealPath());

        return $currentDirectory . DIRECTORY_SEPARATOR . (string) $classNode->name . '.php';
    }
}
