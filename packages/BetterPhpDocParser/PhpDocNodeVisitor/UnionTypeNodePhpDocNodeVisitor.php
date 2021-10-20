<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class UnionTypeNodePhpDocNodeVisitor extends \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor implements \Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface
{
    /**
     * @var \Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;
    /**
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(\Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider $currentTokenIteratorProvider, \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer $attributeMirrorer)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        if (!$node instanceof \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode) {
            return null;
        }
        if ($node instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            return null;
        }
        // unwrap with parent array type...
        $startAndEnd = $node->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END);
        if (!$startAndEnd instanceof \Rector\BetterPhpDocParser\ValueObject\StartAndEnd) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $betterTokenProvider = $this->currentTokenIteratorProvider->provide();
        $isWrappedInCurlyBrackets = $this->isWrappedInCurlyBrackets($betterTokenProvider, $startAndEnd);
        $bracketsAwareUnionTypeNode = new \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode($node->types, $isWrappedInCurlyBrackets);
        $this->attributeMirrorer->mirror($node, $bracketsAwareUnionTypeNode);
        return $bracketsAwareUnionTypeNode;
    }
    private function isWrappedInCurlyBrackets(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $betterTokenProvider, \Rector\BetterPhpDocParser\ValueObject\StartAndEnd $startAndEnd) : bool
    {
        $previousPosition = $startAndEnd->getStart() - 1;
        if ($betterTokenProvider->isTokenTypeOnPosition(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_PARENTHESES, $previousPosition)) {
            return \true;
        }
        // there is no + 1, as end is right at the next token
        return $betterTokenProvider->isTokenTypeOnPosition(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PARENTHESES, $startAndEnd->getEnd());
    }
}
