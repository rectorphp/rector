<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\PhpParser\CurrentNodeProvider;
use Symplify\BetterPhpDocParser\PhpDocInfo\AbstractPhpDocInfoDecorator;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator extends AbstractPhpDocInfoDecorator
{
    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(NamespaceAnalyzer $namespaceAnalyzer, CurrentNodeProvider $currentNodeProvider)
    {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    public function decorate(PhpDocInfo $phpDocInfo): void
    {
        $this->traverseNodes($phpDocInfo->getPhpDocNode());
    }

    protected function traverseNode(Node $node): Node
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

    private function isClassyType(string $name): bool
    {
        return ctype_upper($name[0]);
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
}
