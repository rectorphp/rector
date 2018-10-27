<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Symplify\BetterPhpDocParser\Ast\NodeTraverser;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator
{
    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var Node
     */
    private $currentNode;

    public function __construct(NamespaceAnalyzer $namespaceAnalyzer, NodeTraverser $nodeTraverser)
    {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->nodeTraverser = $nodeTraverser;
    }

    public function decorate(PhpDocInfo $phpDocInfo, Node $node): void
    {
        $this->currentNode = $node;

        $this->nodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), function (PhpDocNode $phpDocNode) {
            return $this->traverseNode($phpDocNode);
        });
    }

    private function traverseNode(PhpDocNode $phpDocNode): PhpDocNode
    {
        if ($this->shouldSkip($phpDocNode)) {
            return $phpDocNode;
        }

        /** @var IdentifierTypeNode $phpDocNode */
        $phpDocNode->name = $this->namespaceAnalyzer->resolveTypeToFullyQualified(
            $phpDocNode->name,
            $this->currentNode
        );

        return $phpDocNode;
    }

    private function shouldSkip(PhpDocNode $phpDocNode): bool
    {
        if (! $phpDocNode instanceof IdentifierTypeNode) {
            return true;
        }
        return ! $this->isClassyType($phpDocNode->name);
    }

    private function isClassyType(string $name): bool
    {
        return ctype_upper($name[0]);
    }
}
