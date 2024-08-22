<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class UseAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsAdder
     */
    private $useImportsAdder;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
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
    public function beforeTraverse(array $nodes) : array
    {
        // no nodes → just return
        if ($nodes === []) {
            return $nodes;
        }
        $rootNode = $this->resolveRootNode($nodes);
        $useImportTypes = $this->useNodesToAddCollector->getObjectImportsByFilePath($this->getFile()->getFilePath());
        $constantUseImportTypes = $this->useNodesToAddCollector->getConstantImportsByFilePath($this->getFile()->getFilePath());
        $functionUseImportTypes = $this->useNodesToAddCollector->getFunctionImportsByFilePath($this->getFile()->getFilePath());
        if ($useImportTypes === [] && $constantUseImportTypes === [] && $functionUseImportTypes === []) {
            return $nodes;
        }
        /** @var FullyQualifiedObjectType[] $useImportTypes */
        $useImportTypes = $this->typeFactory->uniquateTypes($useImportTypes);
        if ($rootNode instanceof FileWithoutNamespace) {
            $nodes = $rootNode->stmts;
        }
        if (!$rootNode instanceof FileWithoutNamespace && !$rootNode instanceof Namespace_) {
            return $nodes;
        }
        return $this->resolveNodesWithImportedUses($nodes, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes, $rootNode);
    }
    public function enterNode(Node $node) : int
    {
        /**
         * We stop the traversal because all the work has already been done in the beforeTraverse() function
         *
         * Using STOP_TRAVERSAL is usually dangerous as it will stop the processing of all your nodes for all visitors
         * but since the PostFileProcessor is using direct new NodeTraverser() and traverse() for only a single
         * visitor per execution, using stop traversal here is safe,
         * ref https://github.com/rectorphp/rector-src/blob/fc1e742fa4d9861ccdc5933f3b53613b8223438d/src/PostRector/Application/PostFileProcessor.php#L59-L61
         */
        return NodeTraverser::STOP_TRAVERSAL;
    }
    /**
     * @param Stmt[] $nodes
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @param FullyQualifiedObjectType[] $constantUseImportTypes
     * @param FullyQualifiedObjectType[] $functionUseImportTypes
     * @return Stmt[]
     * @param \Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_ $namespace
     */
    private function resolveNodesWithImportedUses(array $nodes, array $useImportTypes, array $constantUseImportTypes, array $functionUseImportTypes, $namespace) : array
    {
        // A. has namespace? add under it
        if ($namespace instanceof Namespace_) {
            // then add, to prevent adding + removing false positive of same short use
            $this->useImportsAdder->addImportsToNamespace($namespace, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes);
            return $nodes;
        }
        // B. no namespace? add in the top
        $useImportTypes = $this->filterOutNonNamespacedNames($useImportTypes);
        // then add, to prevent adding + removing false positive of same short use
        return $this->useImportsAdder->addImportsToStmts($namespace, $nodes, $useImportTypes, $constantUseImportTypes, $functionUseImportTypes);
    }
    /**
     * Prevents
     * @param FullyQualifiedObjectType[] $useImportTypes
     * @return FullyQualifiedObjectType[]
     */
    private function filterOutNonNamespacedNames(array $useImportTypes) : array
    {
        $namespacedUseImportTypes = [];
        foreach ($useImportTypes as $useImportType) {
            if (\strpos($useImportType->getClassName(), '\\') === \false) {
                continue;
            }
            $namespacedUseImportTypes[] = $useImportType;
        }
        return $namespacedUseImportTypes;
    }
    /**
     * @param Stmt[] $nodes
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|null
     */
    private function resolveRootNode(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof FileWithoutNamespace || $node instanceof Namespace_) {
                return $node;
            }
        }
        return null;
    }
}
