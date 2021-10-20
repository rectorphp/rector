<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

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
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesCaller;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends \PHPStan\PhpDocParser\Parser\PhpDocParser
{
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesCaller
     */
    private $privatesCaller;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator
     */
    private $doctrineAnnotationDecorator;
    public function __construct(\PHPStan\PhpDocParser\Parser\TypeParser $typeParser, \PHPStan\PhpDocParser\Parser\ConstExprParser $constExprParser, \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory $tokenIteratorFactory, \Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator $doctrineAnnotationDecorator)
    {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->doctrineAnnotationDecorator = $doctrineAnnotationDecorator;
        parent::__construct($typeParser, $constExprParser);
        $this->privatesCaller = new \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesCaller();
    }
    /**
     * @param \PHPStan\PhpDocParser\Parser\TokenIterator $tokenIterator
     */
    public function parse($tokenIterator) : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
    {
        $tokenIterator->consumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_PHPDOC);
        $tokenIterator->tryConsumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL);
        $children = [];
        if (!$tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            while ($tokenIterator->tryConsumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL) && !$tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PHPDOC)) {
                $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            }
        }
        // might be in the middle of annotations
        $tokenIterator->tryConsumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PHPDOC);
        $phpDocNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode($children);
        // replace generic nodes with DoctrineAnnotations
        $this->doctrineAnnotationDecorator->decorate($phpDocNode);
        return $phpDocNode;
    }
    /**
     * @param \PHPStan\PhpDocParser\Parser\TokenIterator $tokenIterator
     */
    public function parseTag($tokenIterator) : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode
    {
        $tag = $this->resolveTag($tokenIterator);
        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode($tag, $phpDocTagValueNode);
    }
    /**
     * @param \PHPStan\PhpDocParser\Parser\TokenIterator $tokenIterator
     * @param string $tag
     */
    public function parseTagValue($tokenIterator, $tag) : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
    {
        $startPosition = $tokenIterator->currentPosition();
        $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        $endPosition = $tokenIterator->currentPosition();
        $startAndEnd = new \Rector\BetterPhpDocParser\ValueObject\StartAndEnd($startPosition, $endPosition);
        $tagValueNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $tagValueNode;
    }
    /**
     * @return PhpDocTextNode|PhpDocTagNode
     */
    private function parseChildAndStoreItsPositions(\PHPStan\PhpDocParser\Parser\TokenIterator $tokenIterator) : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        /** @var PhpDocChildNode $phpDocNode */
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', [$betterTokenIterator]);
        $endPosition = $betterTokenIterator->currentPosition();
        $startAndEnd = new \Rector\BetterPhpDocParser\ValueObject\StartAndEnd($startPosition, $endPosition);
        $phpDocNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $phpDocNode;
    }
    private function resolveTag(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator) : string
    {
        $tag = $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        // there is a space → stop
        if ($tokenIterator->isPrecededByHorizontalWhitespace()) {
            return $tag;
        }
        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if (!$tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_IDENTIFIER)) {
            return $tag;
        }
        // @todo use joinUntil("(")?
        $tag .= $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        return $tag;
    }
}
