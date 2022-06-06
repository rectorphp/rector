<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\ConstExprParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\PhpDocParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TokenIterator;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TypeParser;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use RectorPrefix20220606\Rector\Core\Configuration\CurrentNodeProvider;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesCaller;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
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
     * @var \Symplify\PackageBuilder\Reflection\PrivatesCaller
     */
    private $privatesCaller;
    /**
     * @param PhpDocNodeDecoratorInterface[] $phpDocNodeDecorators
     */
    public function __construct(TypeParser $typeParser, ConstExprParser $constExprParser, CurrentNodeProvider $currentNodeProvider, TokenIteratorFactory $tokenIteratorFactory, array $phpDocNodeDecorators, PrivatesCaller $privatesCaller = null)
    {
        $privatesCaller = $privatesCaller ?? new PrivatesCaller();
        $this->currentNodeProvider = $currentNodeProvider;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->phpDocNodeDecorators = $phpDocNodeDecorators;
        $this->privatesCaller = $privatesCaller;
        parent::__construct($typeParser, $constExprParser);
    }
    public function parse(TokenIterator $tokenIterator) : PhpDocNode
    {
        $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $children = [];
        if (!$tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            while ($tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && !$tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            }
        }
        // might be in the middle of annotations
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);
        $phpDocNode = new PhpDocNode($children);
        // decorate FQN classes etc.
        $node = $this->currentNodeProvider->getNode();
        if (!$node instanceof Node) {
            throw new ShouldNotHappenException();
        }
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
        $startPosition = $tokenIterator->currentPosition();
        $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        $endPosition = $tokenIterator->currentPosition();
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $tagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $tagValueNode;
    }
    /**
     * @return PhpDocTextNode|PhpDocTagNode
     */
    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator) : PhpDocChildNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        /** @var PhpDocChildNode $phpDocNode */
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', [$betterTokenIterator]);
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
