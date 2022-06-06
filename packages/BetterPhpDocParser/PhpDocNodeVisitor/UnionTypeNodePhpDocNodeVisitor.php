<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class UnionTypeNodePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, AttributeMirrorer $attributeMirrorer)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof UnionTypeNode) {
            return null;
        }
        if ($node instanceof BracketsAwareUnionTypeNode) {
            return null;
        }
        $startAndEnd = $this->resolveStardAndEnd($node);
        if (!$startAndEnd instanceof StartAndEnd) {
            return null;
        }
        $betterTokenProvider = $this->currentTokenIteratorProvider->provide();
        $isWrappedInCurlyBrackets = $this->isWrappedInCurlyBrackets($betterTokenProvider, $startAndEnd);
        $bracketsAwareUnionTypeNode = new BracketsAwareUnionTypeNode($node->types, $isWrappedInCurlyBrackets);
        $this->attributeMirrorer->mirror($node, $bracketsAwareUnionTypeNode);
        return $bracketsAwareUnionTypeNode;
    }
    private function isWrappedInCurlyBrackets(BetterTokenIterator $betterTokenProvider, StartAndEnd $startAndEnd) : bool
    {
        $previousPosition = $startAndEnd->getStart() - 1;
        if ($betterTokenProvider->isTokenTypeOnPosition(Lexer::TOKEN_OPEN_PARENTHESES, $previousPosition)) {
            return \true;
        }
        // there is no + 1, as end is right at the next token
        return $betterTokenProvider->isTokenTypeOnPosition(Lexer::TOKEN_CLOSE_PARENTHESES, $startAndEnd->getEnd());
    }
    private function resolveStardAndEnd(UnionTypeNode $unionTypeNode) : ?StartAndEnd
    {
        $starAndEnd = $unionTypeNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        if ($starAndEnd instanceof StartAndEnd) {
            return $starAndEnd;
        }
        // unwrap with parent array type...
        $parent = $unionTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if (!$parent instanceof Node) {
            return null;
        }
        return $parent->getAttribute(PhpDocAttributeKey::START_AND_END);
    }
}
