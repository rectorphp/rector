<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PhpDocNodeMapper
     */
    private $phpDocNodeMapper;

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    /**
     * @var DoctrineAnnotationDecorator
     */
    private $doctrineAnnotationDecorator;

    /**
     * @var TokenIteratorFactory
     */
    private $tokenIteratorFactory;

    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        PhpDocNodeMapper $phpDocNodeMapper,
        TokenIteratorFactory $tokenIteratorFactory,
        AnnotationContentResolver $annotationContentResolver,
        DoctrineAnnotationDecorator $doctrineAnnotationDecorator
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->privatesCaller = new PrivatesCaller();
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->doctrineAnnotationDecorator = $doctrineAnnotationDecorator;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
    }

    public function parse(TokenIterator $tokenIterator): PhpDocNode
    {
        $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);

        $children = [];
        if (! $tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);

            while ($tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && ! $tokenIterator->isCurrentTokenType(
                    Lexer::TOKEN_CLOSE_PHPDOC
                )) {
                $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            }
        }

        // might be in the middle of annotations
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);

        $phpDocNode = new PhpDocNode($children);

        // replace generic nodes with DoctrineAnnotations
        $this->doctrineAnnotationDecorator->decorate($phpDocNode);

        return $phpDocNode;
    }

    /**
     * @param BetterTokenIterator $tokenIterator
     */
    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        $tag = $this->resolveTag($tokenIterator);

        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new PhpDocTagNode($tag, $phpDocTagValueNode);
    }

    /**
     * @param BetterTokenIterator $tokenIterator
     */
    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $startPosition = $tokenIterator->currentTokenOffset();
        $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        $endPosition = $tokenIterator->currentTokenOffset();

        $startAndEnd = new StartAndEnd($startPosition, $endPosition);

        $tagValueNode->setAttribute(StartAndEnd::class, $startAndEnd);

        return $this->phpDocNodeMapper->transform($tagValueNode, $tokenIterator->print());
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): PhpDocChildNode
    {
        $originalTokenIterator = clone $tokenIterator;
        $docContent = $this->annotationContentResolver->resolveFromTokenIterator($originalTokenIterator);

        if (! $tokenIterator instanceof BetterTokenIterator) {
            $tokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        }

        $tokenStart = $tokenIterator->currentPosition();

        /** @var PhpDocChildNode $phpDocNode */
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', [$tokenIterator]);
        $tokenEnd = $tokenIterator->currentPosition();

        $startAndEnd = new StartAndEnd($tokenStart, $tokenEnd);

        $transformedPhpDocNode = $this->phpDocNodeMapper->transform($phpDocNode, $docContent);
        $transformedPhpDocNode->setAttribute(Attribute::START_END, $startAndEnd);

        return $transformedPhpDocNode;
    }

    private function resolveTag(BetterTokenIterator $tokenIterator): string
    {
        $tag = $tokenIterator->currentTokenValue();
        $tokenIterator->next();

        // there is a space → stop
        if ($tokenIterator->isPrecededByHorizontalWhitespace()) {
            return $tag;
        }

        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if (! $tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            return $tag;
        }

        // @todo use joinUntil("(")?
        $tag .= $tokenIterator->currentTokenValue();
        $tokenIterator->next();

        return $tag;
    }
}
