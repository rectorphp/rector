<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitor;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\FileNode;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private UseImportsAdder $useImportsAdder;
    /**
     * @readonly
     */
    private UseNodesToAddCollector $useNodesToAddCollector;
    public function __construct(TypeFactory $typeFactory, UseImportsAdder $useImportsAdder, UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->typeFactory = $typeFactory;
        $this->useImportsAdder = $useImportsAdder;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        // no nodes → just return
        if ($nodes === []) {
            return $nodes;
        }
        $rootNode = $this->resolveRootNode($nodes);
        if (!$rootNode instanceof FileNode && !$rootNode instanceof Namespace_) {
            return $nodes;
        }
        $useImportTypes = $this->useNodesToAddCollector->getObjectImportsByFilePath($this->getFile()->getFilePath());
        $constantUseImportTypes = $this->useNodesToAddCollector->getConstantImportsByFilePath($this->getFile()->getFilePath());
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionImportsByFilePath($this->getFile()->getFilePath());
        if ($useImportTypes === [] && $constantUseImportTypes === [] && $functionUseImportTypes === []) {
            return $nodes;
        }
        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);
        $stmts = $rootNode instanceof FileNode ? $rootNode->stmts : $nodes;
        if ($this->processStmtsWithImportedUses($stmts, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes, $rootNode)) {
            $this->addRectorClassWithLine($rootNode);
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
    /**
     * @param Stmt[] $stmts
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $constantUseImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @param \Rector\PhpParser\Node\FileNode|\PhpParser\Node\Stmt\Namespace_ $namespace
     */
    private function processStmtsWithImportedUses(array $stmts, array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes, $namespace): bool
    {
        // A. has namespace? add under it
        if ($namespace instanceof Namespace_) {
            // then add, to prevent adding + removing false positive of same short use
            return $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes);
        }
        // B. no namespace? add in the top
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use
        return $this->useImportsAdder->addImportsToStmts($namespace, $stmts, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes);
    }
    /**
     * Prevents
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @return FullyQualifiedObjectType[]
     */
    private function filterOutNonNamespacedNames(array $useImportTypes): array
    {
        $namespacedUseImportTypes = [];
        foreach ($useImportTypes as $useImportType) {
            if (strpos($useImportType->getClassName(), '\\') === \false) {
                continue;
            }
            $namespacedUseImportTypes[] = $useImportType;
        }
        return $namespacedUseImportTypes;
    }
    /**
     * @param Stmt[] $nodes
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode|null
     */
    private function resolveRootNode(array $nodes)
    {
        if ($nodes === []) {
            return null;
        }
        $firstStmt = $nodes[0];
        if (!$firstStmt instanceof FileNode) {
            return null;
        }
        foreach ($firstStmt->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                return $stmt;
            }
        }
        return $firstStmt;
    }
}
