<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Attribute;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class TemplatePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
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
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof TemplateTagValueNode) {
            return null;
        }
        if ($node instanceof SpacingAwareTemplateTagValueNode) {
            return null;
        }
        $betterTokenIterator = $this->currentTokenIteratorProvider->provide();
        $startIndex = $node->getAttribute(Attribute::START_INDEX);
        $endIndex = $node->getAttribute(Attribute::END_INDEX);
        if ($startIndex === null || $endIndex === null) {
            throw new ShouldNotHappenException();
        }
        $prepositions = $this->resolvePreposition($betterTokenIterator, $startIndex, $endIndex);
        $spacingAwareTemplateTagValueNode = new SpacingAwareTemplateTagValueNode($node->name, $node->bound, $node->description, $prepositions);
        $this->attributeMirrorer->mirror($node, $spacingAwareTemplateTagValueNode);
        return $spacingAwareTemplateTagValueNode;
    }
    private function resolvePreposition(BetterTokenIterator $betterTokenIterator, int $startIndex, int $endIndex) : string
    {
        $partialTokens = $betterTokenIterator->partialTokens($startIndex, $endIndex);
        foreach ($partialTokens as $partialToken) {
            if ($partialToken[1] !== Lexer::TOKEN_IDENTIFIER) {
                continue;
            }
            if (!\in_array($partialToken[0], ['as', 'of'], \true)) {
                continue;
            }
            return $partialToken[0];
        }
        return 'of';
    }
}
