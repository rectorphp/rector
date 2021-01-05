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
use Rector\BetterPhpDocParser\Contract\GenericPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\TagToPhpDocNodeFactoryMatcher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Mapper\NodeMapper;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var PhpDocNodeFactoryInterface[]
     */
    private $phpDocNodeFactories = [];

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
     * @param PhpDocNodeFactoryInterface[] $phpDocNodeFactories
     */
    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        TagToPhpDocNodeFactoryMatcher $tagToPhpDocNodeFactoryMatcher,
        Lexer $lexer,
        AnnotationContentResolver $annotationContentResolver,
        NodeMapper $nodeMapper,
        array $phpDocNodeFactories = []
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->privatesCaller = new PrivatesCaller();
        $this->privatesAccessor = new PrivatesAccessor();
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->lexer = $lexer;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->nodeMapper = $nodeMapper;

        $this->setPhpDocNodeFactories($phpDocNodeFactories);
        $this->tagToPhpDocNodeFactoryMatcher = $tagToPhpDocNodeFactoryMatcher;
    }

    public function parseString(string $docBlock): PhpDocNode
    {
        $tokens = $this->lexer->tokenize($docBlock);
        $tokenIterator = new TokenIterator($tokens);

        return parent::parse($tokenIterator);
    }

    /**
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

        $phpDocNode = new PhpDocNode(array_values($children));
        return $this->nodeMapper->mapNode($phpDocNode);
    }

    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        $tag = $this->resolveTag($tokenIterator);

        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new PhpDocTagNode($tag, $phpDocTagValueNode);
    }

    /**
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
            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
                $tag,
                $currentPhpNode
            );

            $tagValueNode = $phpDocNodeFactory->createFromNodeAndTokens(
                $currentPhpNode,
                $tokenIterator,
                $fullyQualifiedAnnotationClass
            );
        }

        // fallback to original parser
        if ($tagValueNode === null) {
            $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        }

        return $this->nodeMapper->mapNode($tagValueNode);
    }

    /**
     * @param PhpDocNodeFactoryInterface[] $phpDocNodeFactories
     */
    private function setPhpDocNodeFactories(array $phpDocNodeFactories): void
    {
        foreach ($phpDocNodeFactories as $phpDocNodeFactory) {
            $classes = $this->resolvePhpDocNodeFactoryClasses($phpDocNodeFactory);
            foreach ($classes as $class) {
                $this->phpDocNodeFactories[$class] = $phpDocNodeFactory;
            }
        }
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): Node
    {
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', $tokenIterator);
        return $this->nodeMapper->mapNode($phpDocNode);
    }

    /**
     * @return string[]
     */
    private function resolvePhpDocNodeFactoryClasses(PhpDocNodeFactoryInterface $phpDocNodeFactory): array
    {
        if ($phpDocNodeFactory instanceof SpecificPhpDocNodeFactoryInterface) {
            return $phpDocNodeFactory->getClasses();
        }

        if ($phpDocNodeFactory instanceof GenericPhpDocNodeFactoryInterface) {
            return $phpDocNodeFactory->getTagValueNodeClassesToAnnotationClasses();
        }

        throw new ShouldNotHappenException();
    }
}
