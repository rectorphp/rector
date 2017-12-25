<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node;
use Rector\BetterReflection\TypesFinder\PhpDocumentor\NamespaceNodeToReflectionTypeContext;
use Rector\Node\Attribute;
use SplObjectStorage;
use Symplify\TokenRunner\ReflectionDocBlock\CleanDocBlockFactory;

final class DocBlockFactory
{
    /**
     * @var DocBlock[]|SplObjectStorage
     */
    private $docBlocksPerNode;

    /**
     * @var NamespaceNodeToReflectionTypeContext
     */
    private $namespaceNodeToReflectionTypeContext;

    /**
     * @var CleanDocBlockFactory
     */
    private $cleanDocBlockFactory;

    public function __construct(
        NamespaceNodeToReflectionTypeContext $namespaceNodeToReflectionTypeContext,
        CleanDocBlockFactory $cleanDocBlockFactory
    ) {
        $this->docBlocksPerNode = new SplObjectStorage();
        $this->namespaceNodeToReflectionTypeContext = $namespaceNodeToReflectionTypeContext;
        $this->cleanDocBlockFactory = $cleanDocBlockFactory;
    }

    public function createFromNode(Node $node): DocBlock
    {
        if (isset($this->docBlocksPerNode[$node])) {
            return $this->docBlocksPerNode[$node];
        }

        $docBlockContent = $node->getDocComment() ? $node->getDocComment()->getText() : ' ';
        $docBlockContext = $this->createContextForNamespace($node);

        return $this->docBlocksPerNode[$node] = $this->cleanDocBlockFactory->create($docBlockContent, $docBlockContext);
    }

    private function createContextForNamespace(Node $node): ?Context
    {
        $namespaceNode = $node->getAttribute(Attribute::NAMESPACE_NODE);
        if ($namespaceNode === null) {
            return null;
        }

        return ($this->namespaceNodeToReflectionTypeContext)($namespaceNode);
    }
}
