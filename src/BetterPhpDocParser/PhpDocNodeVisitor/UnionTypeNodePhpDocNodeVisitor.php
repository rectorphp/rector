<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
final class UnionTypeNodePhpDocNodeVisitor extends AbstractNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     */
    private CurrentTokenIteratorProvider $currentTokenIteratorProvider;
    /**
     * @readonly
     */
    private AttributeMirrorer $attributeMirrorer;
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, AttributeMirrorer $attributeMirrorer)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof UnionTypeNode) {
            return null;
        }
        if ($node instanceof BracketsAwareUnionTypeNode) {
            return null;
        }
        $startAndEnd = $this->resolveStartAndEnd($node);
        if (!$startAndEnd instanceof StartAndEnd) {
            $firstKey = array_key_first($node->types);
            $lastKey = array_key_last($node->types);
            $startAndEnd = new StartAndEnd($node->types[$firstKey]->getAttribute('startIndex'), $node->types[$lastKey]->getAttribute('endIndex'));
        }
        $tokenIterator = $this->currentTokenIteratorProvider->provide();
        $isWrappedInCurlyBrackets = $this->isWrappedInCurlyBrackets($tokenIterator, $startAndEnd);
        $bracketsAwareUnionTypeNode = new BracketsAwareUnionTypeNode($node->types, $isWrappedInCurlyBrackets);
        $this->attributeMirrorer->mirror($node, $bracketsAwareUnionTypeNode);
        return $bracketsAwareUnionTypeNode;
    }
    private function isWrappedInCurlyBrackets(TokenIterator $tokenIterator, StartAndEnd $startAndEnd): bool
    {
        $previousPosition = $startAndEnd->getStart() - 1;
        $nextPosition = $startAndEnd->getEnd() + 1;
        // A union is wrapped only when BOTH parens flank it. Either alone may be unrelated —
        // e.g. an `@method`'s parameter-list `(` before a first-position union, or the matching
        // `)` after a last-position union — neither belongs to the union type itself.
        $tokens = $tokenIterator->getTokens();
        return ($tokens[$previousPosition][Lexer::TYPE_OFFSET] ?? null) === Lexer::TOKEN_OPEN_PARENTHESES && ($tokens[$nextPosition][Lexer::TYPE_OFFSET] ?? null) === Lexer::TOKEN_CLOSE_PARENTHESES;
    }
    private function resolveStartAndEnd(UnionTypeNode $unionTypeNode): ?StartAndEnd
    {
        $starAndEnd = $unionTypeNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        if ($starAndEnd instanceof StartAndEnd) {
            return $starAndEnd;
        }
        // unwrap with parent array type...
        $parentNode = $unionTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if (!$parentNode instanceof Node) {
            return null;
        }
        return $parentNode->getAttribute(PhpDocAttributeKey::START_AND_END);
    }
}
