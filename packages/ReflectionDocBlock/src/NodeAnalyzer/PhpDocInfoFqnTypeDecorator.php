<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use PhpParser\Node as PhpParserNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Symplify\BetterPhpDocParser\PhpDocInfo\AbstractPhpDocInfoDecorator;
use Symplify\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator extends AbstractPhpDocInfoDecorator
{
    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var PhpParserNode|null
     */
    private $phpParserNode;

    public function __construct(NamespaceAnalyzer $namespaceAnalyzer)
    {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    public function setCurrentPhpParserNode(PhpParserNode $phpParserNode)
    {
        $this->phpParserNode = $phpParserNode;
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

        if ($this->phpParserNode === null) {
            // @todo throw exception with info?
            return $node;
        }

        /** @var IdentifierTypeNode $node */
        $node->name = $this->namespaceAnalyzer->resolveTypeToFullyQualified($node->name, $this->phpParserNode);

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
