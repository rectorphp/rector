<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\TagResolver;
use Rector\BetterPhpDocParser\TagToPhpDocNodeFactoryMatcher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Mapper\NodeMapper;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocNode;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocTagNode;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    /**
     * @var NodeMapper
     */
    private $nodeMapper;

    /**
     * @var TagToPhpDocNodeFactoryMatcher
     */
    private $tagToPhpDocNodeFactoryMatcher;

    /**
     * @var TagResolver
     */
    private $tagResolver;

    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        TagToPhpDocNodeFactoryMatcher $tagToPhpDocNodeFactoryMatcher,
        Lexer $lexer,
        AnnotationContentResolver $annotationContentResolver,
        PrivatesCaller $privatesCaller,
        PrivatesAccessor $privatesAccessor,
        TagResolver $tagResolver,
        NodeMapper $nodeMapper
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->lexer = $lexer;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->nodeMapper = $nodeMapper;
        $this->tagToPhpDocNodeFactoryMatcher = $tagToPhpDocNodeFactoryMatcher;
        $this->privatesCaller = $privatesCaller;
        $this->privatesAccessor = $privatesAccessor;
        $this->tagResolver = $tagResolver;
    }

    public function parseString(string $docBlock): PhpDocNode
    {
        $tokens = $this->lexer->tokenize($docBlock);
        $tokenIterator = new TokenIterator($tokens);

        return parent::parse($tokenIterator);
    }

    /**
     * @param SmartTokenIterator $tokenIterator
     * @return AttributeAwareInterface&PhpDocNode
     */
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

        $phpDocNode = new AttributeAwarePhpDocNode(array_values($children));
        return $this->nodeMapper->mapNode($phpDocNode);
    }

    /**
     * @param SmartTokenIterator $tokenIterator
     * @return PhpDocTagNode&AttributeAwareInterface
     */
    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        $tag = $this->tagResolver->resolveTag($tokenIterator);

        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new AttributeAwarePhpDocTagNode($tag, $phpDocTagValueNode);
    }

    /**
     * @param SmartTokenIterator $tokenIterator
     * @return PhpDocTagValueNode&AttributeAwareInterface
     */
    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $tagValueNode = null;

        $currentPhpNode = $this->currentNodeProvider->getNode();
        if ($currentPhpNode === null) {
            throw new ShouldNotHappenException();
        }

        // class-annotation
        $phpDocNodeFactory = $this->tagToPhpDocNodeFactoryMatcher->match($tag);
        if ($phpDocNodeFactory !== null) {
            $resolvedTag = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($tag, $currentPhpNode);
            $tagValueNode = $phpDocNodeFactory->create($tokenIterator, $resolvedTag);
        }

        // fallback to original parser
        if ($tagValueNode === null) {
            $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        }

        return $this->nodeMapper->mapNode($tagValueNode);
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): Node
    {
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', $tokenIterator);
        return $this->nodeMapper->mapNode($phpDocNode);
    }
}
