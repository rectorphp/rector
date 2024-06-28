<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
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
