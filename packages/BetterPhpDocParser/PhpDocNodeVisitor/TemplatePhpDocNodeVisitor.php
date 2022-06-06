<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class TemplatePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
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
        if (!$node instanceof TemplateTagValueNode) {
            return null;
        }
        if ($node instanceof SpacingAwareTemplateTagValueNode) {
            return null;
        }
        $betterTokenIterator = $this->currentTokenIteratorProvider->provide();
        $startAndEnd = $node->getAttribute(PhpDocAttributeKey::START_AND_END);
        if (!$startAndEnd instanceof StartAndEnd) {
            throw new ShouldNotHappenException();
        }
        $prepositions = $this->resolvePreposition($betterTokenIterator, $startAndEnd);
        $spacingAwareTemplateTagValueNode = new SpacingAwareTemplateTagValueNode($node->name, $node->bound, $node->description, $prepositions);
        $this->attributeMirrorer->mirror($node, $spacingAwareTemplateTagValueNode);
        return $spacingAwareTemplateTagValueNode;
    }
    private function resolvePreposition(BetterTokenIterator $betterTokenIterator, StartAndEnd $startAndEnd) : string
    {
        $partialTokens = $betterTokenIterator->partialTokens($startAndEnd->getStart(), $startAndEnd->getEnd());
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
