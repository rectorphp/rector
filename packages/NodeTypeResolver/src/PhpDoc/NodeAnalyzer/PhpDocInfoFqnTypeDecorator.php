<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\PhpParser\CurrentNodeProvider;
use Symplify\BetterPhpDocParser\Ast\NodeTraverser;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator
{
    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(
        NamespaceAnalyzer $namespaceAnalyzer,
        CurrentNodeProvider $currentNodeProvider,
        NodeTraverser $nodeTraverser
    ) {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->nodeTraverser = $nodeTraverser;
    }

    public function decorate(PhpDocInfo $phpDocInfo): void
    {
        $this->nodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), function (Node $node) {
            return $this->traverseNode($node);
        });
    }

    private function traverseNode(Node $node): Node
    {
        if ($this->shouldSkip($node)) {
            return $node;
        }

        if ($this->currentNodeProvider->getCurrentNode() === null) {
            return $node;
        }

        /** @var IdentifierTypeNode $node */
        $node->name = $this->namespaceAnalyzer->resolveTypeToFullyQualified(
            $node->name,
            $this->currentNodeProvider->getCurrentNode()
        );

        return $node;
    }

    private function shouldSkip(Node $node): bool
    {
        if (! $node instanceof IdentifierTypeNode) {
            return true;
        }

        if (! $this->isClassyType($node->name)) {
            return true;
        }

        return false;
    }

    private function isClassyType(string $name): bool
    {
        return ctype_upper($name[0]);
    }
}
