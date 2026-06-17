<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\FileNode;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param Stmt[] $stmts
     */
    #[Override]
    public function shouldTraverse(array $stmts): bool
    {
        $fileNode = $stmts[0] ?? null;
        if (!$fileNode instanceof FileNode) {
            return \false;
        }
        return $fileNode->getPendingImports()->hasPendingUseImports();
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        /** @var FileNode $fileNode */
        $fileNode = $nodes[0] ?? null;
        $pendingImports = $fileNode->getPendingImports();
        $useImportTypes = $pendingImports->getUseImports();
        $constantUseImportTypes = $pendingImports->getConstantImports();
        $functionUseImportTypes = $pendingImports->getFunctionImports();
        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);
        // the FileNode owns its used imports and keeps them in sync, so the run converges
        // without re-resolving on each traversal iteration
        if ($fileNode->addImports($useImportTypes, $constantUseImportTypes, $functionUseImportTypes)) {
            $this->addRectorClassWithLine($fileNode);
        }
        return $nodes;
    }
    public function enterNode(Node $node): int
    {
        /**
         * We stop the traversal because all the work has already been done in the beforeTraverse() function
         *
         * Using STOP_TRAVERSAL is usually dangerous as it will stop the processing of all your nodes for all visitors
         * but since the PostFileProcessor is using direct new NodeTraverser() and traverse() for only a single
         * visitor per execution, using stop traversal here is safe,
         * ref https://github.com/rectorphp/rector-src/blob/fc1e742fa4d9861ccdc5933f3b53613b8223438d/src/PostRector/Application/PostFileProcessor.php#L59-L61
         */
        return NodeVisitor::STOP_TRAVERSAL;
    }
}
