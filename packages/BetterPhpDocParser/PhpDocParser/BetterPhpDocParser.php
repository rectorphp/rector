<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\Reflection\PrivatesAccessor;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    /**
     * @var PhpDocNodeDecoratorInterface[]
     * @readonly
     */
    private $phpDocNodeDecorators;
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @param PhpDocNodeDecoratorInterface[] $phpDocNodeDecorators
     */
    public function __construct(TypeParser $typeParser, ConstExprParser $constExprParser, TokenIteratorFactory $tokenIteratorFactory, array $phpDocNodeDecorators, PrivatesAccessor $privatesAccessor)
    {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->phpDocNodeDecorators = $phpDocNodeDecorators;
        $this->privatesAccessor = $privatesAccessor;
        parent::__construct(
            // TypeParser
            $typeParser,
            // ConstExprParser
            $constExprParser,
            // requireWhitespaceBeforeDescription
            \false,
            // preserveTypeAliasesWithInvalidTypes
            \false,
            // usedAttributes
            ['lines' => \true, 'indexes' => \true],
            // parseDoctrineAnnotations
            \false,
            // textBetweenTagsBelongsToDescription, default to false, exists since 1.23.0
            \true
        );
    }
    public function parseWithNode(BetterTokenIterator $betterTokenIterator, Node $node) : PhpDocNode
    {
        $betterTokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        $betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $children = [];
        if (!$betterTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($betterTokenIterator);
            while ($betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && !$betterTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                $children[] = $this->parseChildAndStoreItsPositions($betterTokenIterator);
            }
        }
        // might be in the middle of annotations
        $betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);
        $phpDocNode = new PhpDocNode($children);
        foreach ($this->phpDocNodeDecorators as $phpDocNodeDecorator) {
            $phpDocNodeDecorator->decorate($phpDocNode, $node);
        }
        return $phpDocNode;
    }
    public function parseTag(TokenIterator $tokenIterator) : PhpDocTagNode
    {
        // replace generic nodes with DoctrineAnnotations
        if (!$tokenIterator instanceof BetterTokenIterator) {
            throw new ShouldNotHappenException();
        }
        $tag = $this->resolveTag($tokenIterator);
        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new PhpDocTagNode($tag, $phpDocTagValueNode);
    }
    /**
     * @param BetterTokenIterator $tokenIterator
     */
    public function parseTagValue(TokenIterator $tokenIterator, string $tag) : PhpDocTagValueNode
    {
        $isPrecededByHorizontalWhitespace = $tokenIterator->isPrecededByHorizontalWhitespace();
        $startPosition = $tokenIterator->currentPosition();
        $phpDocTagValueNode = parent::parseTagValue($tokenIterator, $tag);
        $endPosition = $tokenIterator->currentPosition();
        if ($isPrecededByHorizontalWhitespace && \property_exists($phpDocTagValueNode, 'description')) {
            $phpDocTagValueNode->description = \str_replace("\n", "\n * ", (string) $phpDocTagValueNode->description);
        }
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $phpDocTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $phpDocTagValueNode;
    }
    /**
     * @return PhpDocTextNode|PhpDocTagNode
     */
    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator) : PhpDocChildNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        /** @var PhpDocChildNode $phpDocNode */
        $phpDocNode = $this->privatesAccessor->callPrivateMethod($this, 'parseChild', [$betterTokenIterator]);
        $endPosition = $betterTokenIterator->currentPosition();
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $phpDocNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $phpDocNode;
    }
    private function resolveTag(BetterTokenIterator $tokenIterator) : string
    {
        $tag = $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        // there is a space → stop
        if ($tokenIterator->isPrecededByHorizontalWhitespace()) {
            return $tag;
        }
        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if (!$tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            return $tag;
        }
        // @todo use joinUntil("(")?
        $tag .= $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        return $tag;
    }
}
